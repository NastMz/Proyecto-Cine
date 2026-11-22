-- Seed 002 · Demo programming and sales history.
--
-- fn_demo_schedule fills every active hall with four daily slots. It is
-- idempotent (overlapping slots are skipped through the exclusion
-- constraint), so `php bin/console demo:schedule` can run on every start to
-- keep a rolling week of showtimes in the demo.

CREATE OR REPLACE FUNCTION fn_demo_schedule(p_from date, p_days integer)
RETURNS integer
LANGUAGE plpgsql AS $$
DECLARE
    v_movies   integer[];
    v_inserted integer;
BEGIN
    SELECT array_agg(movie_id ORDER BY movie_id) INTO v_movies
    FROM movie WHERE is_active;

    IF v_movies IS NULL THEN
        RETURN 0;
    END IF;

    INSERT INTO showtime (movie_id, hall_id, starts_at, ends_at, language)
    SELECT v_movies[1 + ((h.hall_id * 3 + d * 2 + s.slot) % cardinality(v_movies))],
           h.hall_id,
           ((p_from + d) + s.at) AT TIME ZONE 'America/Bogota',
           -- placeholder, recalculated by trg_showtime_set_end
           ((p_from + d) + s.at) AT TIME ZONE 'America/Bogota' + interval '1 minute',
           CASE WHEN (h.hall_id + d + s.slot) % 3 = 0 THEN 'Subtitulada' ELSE 'Doblada' END
    FROM hall h
    CROSS JOIN generate_series(0, p_days - 1) AS d
    CROSS JOIN (VALUES (0, time '13:00'), (1, time '16:00'), (2, time '19:00'), (3, time '22:00'))
        AS s(slot, at)
    WHERE h.is_active
    ON CONFLICT DO NOTHING;

    GET DIAGNOSTICS v_inserted = ROW_COUNT;
    RETURN v_inserted;
END;
$$;

-- Last six days (history for the dashboard) and the coming week.
SELECT fn_demo_schedule(CURRENT_DATE - 6, 13);

-- Sales history for past showtimes. Inserted directly because
-- fn_purchase_tickets (correctly) refuses showtimes that already started.
DO $$
DECLARE
    st          record;
    v_customers integer[];
    v_methods   smallint[];
    v_user      integer;
    v_purchase  bigint;
    v_price     numeric;
    v_group     integer;
    v_groups    integer;
    v_size      integer;
BEGIN
    SELECT array_agg(u.user_id ORDER BY u.user_id) INTO v_customers
    FROM app_user u JOIN role r ON r.role_id = u.role_id WHERE r.code = 'cliente';
    SELECT array_agg(payment_method_id ORDER BY payment_method_id) INTO v_methods FROM payment_method;

    FOR st IN
        SELECT showtime_id, hall_id, starts_at FROM showtime
        WHERE starts_at < now() ORDER BY starts_at
    LOOP
        -- 2 to 6 groups of 1 to 4 people per showtime.
        v_groups := 2 + st.showtime_id % 5;
        FOR v_group IN 1 .. v_groups LOOP
            v_user  := v_customers[1 + (st.showtime_id + v_group) % cardinality(v_customers)];
            v_price := fn_ticket_price(st.showtime_id, v_user);
            v_size  := 1 + (st.showtime_id * 3 + v_group * 5) % 4;

            INSERT INTO purchase (user_id, showtime_id, payment_method_id, total, purchased_at)
            VALUES (v_user, st.showtime_id, v_methods[1 + (st.showtime_id + v_group) % cardinality(v_methods)],
                    v_price * v_size, st.starts_at - make_interval(hours => v_group * 5))
            RETURNING purchase_id INTO v_purchase;

            -- Best free seats first: centre rows, centre columns.
            INSERT INTO ticket (purchase_id, showtime_id, seat_id, unit_price)
            SELECT v_purchase, st.showtime_id, s.seat_id, v_price
            FROM seat s
            WHERE s.hall_id = st.hall_id
              AND NOT EXISTS (SELECT 1 FROM ticket t WHERE t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id)
            ORDER BY abs(ascii(s.row_label) - 68), abs(s.seat_number - 6), s.seat_id
            LIMIT v_size;
        END LOOP;
    END LOOP;
END;
$$;

-- A few upcoming purchases through the real purchase function.
SELECT fn_purchase_tickets(
           (SELECT user_id FROM app_user WHERE email = 'laura@butaca.test'),
           st.showtime_id,
           ARRAY(SELECT s.seat_id FROM seat s
                 WHERE s.hall_id = st.hall_id AND s.row_label = 'D' AND s.seat_number BETWEEN 5 AND 6),
           (SELECT payment_method_id FROM payment_method WHERE name = 'Billetera digital'))
FROM (SELECT showtime_id, hall_id FROM showtime
      WHERE starts_at > now() + interval '1 hour'
      ORDER BY starts_at, hall_id LIMIT 3) st;

SELECT fn_purchase_tickets(
           (SELECT user_id FROM app_user WHERE email = 'andres@butaca.test'),
           st.showtime_id,
           ARRAY(SELECT s.seat_id FROM seat s
                 WHERE s.hall_id = st.hall_id AND s.row_label = 'C' AND s.seat_number BETWEEN 3 AND 6),
           (SELECT payment_method_id FROM payment_method WHERE name = 'Tarjeta de crédito'))
FROM (SELECT showtime_id, hall_id FROM showtime
      WHERE starts_at > now() + interval '1 hour'
      ORDER BY starts_at, hall_id OFFSET 3 LIMIT 4) st;
