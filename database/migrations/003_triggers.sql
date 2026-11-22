-- 003 · Triggers

CREATE TRIGGER trg_app_user_touch
    BEFORE UPDATE ON app_user
    FOR EACH ROW EXECUTE FUNCTION fn_touch_updated_at();

CREATE TRIGGER trg_movie_touch
    BEFORE UPDATE ON movie
    FOR EACH ROW EXECUTE FUNCTION fn_touch_updated_at();

-- AFTER: the hall row must exist before its seats reference it.
CREATE TRIGGER trg_hall_sync_seats
    AFTER INSERT OR UPDATE OF seat_rows, seats_per_row ON hall
    FOR EACH ROW EXECUTE FUNCTION fn_hall_sync_seats();

CREATE TRIGGER trg_showtime_set_end
    BEFORE INSERT OR UPDATE OF movie_id, starts_at ON showtime
    FOR EACH ROW EXECUTE FUNCTION fn_showtime_set_end();

CREATE TRIGGER trg_ticket_validate
    BEFORE INSERT OR UPDATE OF seat_id, showtime_id ON ticket
    FOR EACH ROW EXECUTE FUNCTION fn_ticket_validate();
