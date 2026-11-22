-- 002 · Stored functions (business rules that live in the database)
--
-- Custom SQLSTATE codes raised here (mapped to friendly messages by the app):
--   CN001  one or more seats are already sold
--   CN002  the showtime is not open for sale (already started)
--   CN003  invalid purchase request (empty, too many or foreign seats)
--   CN004  the user or the payment method is not active
--   CN005  hall layout cannot change because it has sold tickets

-- Keeps updated_at current on every UPDATE.
CREATE FUNCTION fn_touch_updated_at() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
    NEW.updated_at := now();
    RETURN NEW;
END;
$$;

-- Builds the seat map of a hall: rows A.. and numbers 1..n.
-- Last row is "preferential"; both ends of the first row are "accessible".
CREATE FUNCTION fn_generate_seats(p_hall_id smallint) RETURNS integer
LANGUAGE plpgsql AS $$
DECLARE
    v_rows  smallint;
    v_cols  smallint;
    v_count integer;
BEGIN
    SELECT seat_rows, seats_per_row INTO v_rows, v_cols
    FROM hall WHERE hall_id = p_hall_id;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Hall % does not exist', p_hall_id USING ERRCODE = 'CN003';
    END IF;

    DELETE FROM seat WHERE hall_id = p_hall_id;

    INSERT INTO seat (hall_id, row_label, seat_number, seat_kind)
    SELECT p_hall_id,
           chr(64 + r),
           c,
           CASE
               WHEN r = v_rows AND v_rows > 1 THEN 'preferential'
               WHEN r = 1 AND (c = 1 OR c = v_cols) THEN 'accessible'
               ELSE 'standard'
           END
    FROM generate_series(1, v_rows) AS r
    CROSS JOIN generate_series(1, v_cols) AS c;

    GET DIAGNOSTICS v_count = ROW_COUNT;
    RETURN v_count;
END;
$$;

-- Trigger: generate seats for new halls and regenerate them when the layout
-- changes, but only while no ticket has been sold for that hall.
CREATE FUNCTION fn_hall_sync_seats() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
    IF TG_OP = 'UPDATE'
       AND NEW.seat_rows = OLD.seat_rows
       AND NEW.seats_per_row = OLD.seats_per_row THEN
        RETURN NEW;
    END IF;

    IF TG_OP = 'UPDATE' AND EXISTS (
        SELECT 1 FROM ticket t JOIN seat s ON s.seat_id = t.seat_id
        WHERE s.hall_id = NEW.hall_id
    ) THEN
        RAISE EXCEPTION 'Hall % already has sold tickets; its layout cannot change', NEW.hall_id
            USING ERRCODE = 'CN005';
    END IF;

    PERFORM fn_generate_seats(NEW.hall_id);
    RETURN NEW;
END;
$$;

-- Trigger: a showtime ends after the movie plus 20 minutes of cleaning.
CREATE FUNCTION fn_showtime_set_end() RETURNS trigger
LANGUAGE plpgsql AS $$
DECLARE
    v_duration smallint;
BEGIN
    SELECT duration_min INTO v_duration FROM movie WHERE movie_id = NEW.movie_id;
    IF v_duration IS NULL THEN
        RAISE EXCEPTION 'Movie % does not exist', NEW.movie_id USING ERRCODE = '23503';
    END IF;
    NEW.ends_at := NEW.starts_at + make_interval(mins => v_duration + 20);
    RETURN NEW;
END;
$$;

-- Effective ticket price for a user: hall price (or showtime override)
-- minus the discount of the user's type, rounded to whole pesos.
CREATE FUNCTION fn_ticket_price(p_showtime_id integer, p_user_id integer)
RETURNS numeric
LANGUAGE sql STABLE AS $$
    SELECT round(
               COALESCE(st.price_override, h.base_price)
               * (1 - COALESCE(ut.discount_pct, 0) / 100.0),
               0)
    FROM showtime st
    JOIN hall h ON h.hall_id = st.hall_id
    LEFT JOIN app_user u ON u.user_id = p_user_id
    LEFT JOIN user_type ut ON ut.user_type_id = u.user_type_id
    WHERE st.showtime_id = p_showtime_id;
$$;

-- Trigger: a ticket's seat must belong to the showtime's hall.
CREATE FUNCTION fn_ticket_validate() RETURNS trigger
LANGUAGE plpgsql AS $$
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM showtime st
        JOIN seat s ON s.hall_id = st.hall_id
        WHERE st.showtime_id = NEW.showtime_id AND s.seat_id = NEW.seat_id
    ) THEN
        RAISE EXCEPTION 'Seat % does not belong to the hall of showtime %', NEW.seat_id, NEW.showtime_id
            USING ERRCODE = 'CN003';
    END IF;
    RETURN NEW;
END;
$$;

-- Buys several seats for one showtime in a single atomic operation and
-- returns the new purchase id. Concurrent buyers of the same showtime are
-- serialized by locking the showtime row.
CREATE FUNCTION fn_purchase_tickets(
    p_user_id           integer,
    p_showtime_id       integer,
    p_seat_ids          integer[],
    p_payment_method_id smallint
) RETURNS bigint
LANGUAGE plpgsql AS $$
DECLARE
    v_starts_at   timestamptz;
    v_hall_id     smallint;
    v_seat_count  integer;
    v_price       numeric;
    v_purchase_id bigint;
    v_taken       text;
BEGIN
    v_seat_count := COALESCE(cardinality(p_seat_ids), 0);
    IF v_seat_count = 0 OR v_seat_count > 10 THEN
        RAISE EXCEPTION 'Select between 1 and 10 seats' USING ERRCODE = 'CN003';
    END IF;
    IF (SELECT count(DISTINCT x) FROM unnest(p_seat_ids) AS x) <> v_seat_count THEN
        RAISE EXCEPTION 'Duplicated seats in request' USING ERRCODE = 'CN003';
    END IF;

    SELECT starts_at, hall_id INTO v_starts_at, v_hall_id
    FROM showtime WHERE showtime_id = p_showtime_id
    FOR UPDATE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Showtime % does not exist', p_showtime_id USING ERRCODE = 'CN003';
    END IF;
    IF v_starts_at <= now() THEN
        RAISE EXCEPTION 'Showtime % already started', p_showtime_id USING ERRCODE = 'CN002';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM app_user WHERE user_id = p_user_id AND is_active)
       OR NOT EXISTS (SELECT 1 FROM payment_method
                      WHERE payment_method_id = p_payment_method_id AND is_active) THEN
        RAISE EXCEPTION 'Inactive user or payment method' USING ERRCODE = 'CN004';
    END IF;

    IF (SELECT count(*) FROM seat WHERE hall_id = v_hall_id AND seat_id = ANY (p_seat_ids))
       <> v_seat_count THEN
        RAISE EXCEPTION 'Some seats do not belong to this hall' USING ERRCODE = 'CN003';
    END IF;

    SELECT string_agg(s.row_label || s.seat_number, ', ' ORDER BY s.row_label, s.seat_number)
    INTO v_taken
    FROM ticket t JOIN seat s ON s.seat_id = t.seat_id
    WHERE t.showtime_id = p_showtime_id AND t.seat_id = ANY (p_seat_ids);

    IF v_taken IS NOT NULL THEN
        RAISE EXCEPTION 'Seats already sold: %', v_taken USING ERRCODE = 'CN001';
    END IF;

    v_price := fn_ticket_price(p_showtime_id, p_user_id);

    INSERT INTO purchase (user_id, showtime_id, payment_method_id, total)
    VALUES (p_user_id, p_showtime_id, p_payment_method_id, v_price * v_seat_count)
    RETURNING purchase_id INTO v_purchase_id;

    INSERT INTO ticket (purchase_id, showtime_id, seat_id, unit_price)
    SELECT v_purchase_id, p_showtime_id, x, v_price
    FROM unnest(p_seat_ids) AS x;

    RETURN v_purchase_id;
END;
$$;
