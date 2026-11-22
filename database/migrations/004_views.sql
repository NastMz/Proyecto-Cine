-- 004 · Views used by the application and the reports

-- Users with their role and type (successor of the original "usersView").
CREATE VIEW v_user_detail AS
SELECT u.user_id,
       u.document,
       u.first_name,
       u.last_name,
       u.first_name || ' ' || u.last_name AS full_name,
       u.email,
       u.phone,
       u.is_active,
       u.created_at,
       r.role_id,
       r.code AS role_code,
       r.name AS role_name,
       ut.user_type_id,
       ut.name AS user_type_name,
       ut.discount_pct
FROM app_user u
JOIN role r ON r.role_id = u.role_id
JOIN user_type ut ON ut.user_type_id = u.user_type_id;

-- Capacity, sold seats and price of every showtime.
CREATE VIEW v_showtime_availability AS
SELECT st.showtime_id,
       st.movie_id,
       m.title AS movie_title,
       m.rating,
       st.hall_id,
       h.name AS hall_name,
       h.format,
       st.starts_at,
       st.ends_at,
       st.language,
       COALESCE(st.price_override, h.base_price) AS price,
       cap.capacity,
       COALESCE(sold.sold, 0) AS sold,
       cap.capacity - COALESCE(sold.sold, 0) AS available,
       round(100.0 * COALESCE(sold.sold, 0) / NULLIF(cap.capacity, 0), 1) AS occupancy_pct
FROM showtime st
JOIN movie m ON m.movie_id = st.movie_id
JOIN hall h ON h.hall_id = st.hall_id
JOIN LATERAL (SELECT count(*)::integer AS capacity FROM seat s WHERE s.hall_id = st.hall_id) cap ON true
LEFT JOIN LATERAL (SELECT count(*)::integer AS sold FROM ticket t WHERE t.showtime_id = st.showtime_id) sold ON true;

-- Box office per movie.
CREATE VIEW v_movie_sales AS
SELECT m.movie_id,
       m.title,
       count(t.ticket_id)::integer AS tickets_sold,
       COALESCE(sum(t.unit_price), 0) AS revenue
FROM movie m
LEFT JOIN showtime st ON st.movie_id = m.movie_id
LEFT JOIN ticket t ON t.showtime_id = st.showtime_id
GROUP BY m.movie_id, m.title;

-- One row per purchase with everything a receipt needs.
CREATE VIEW v_purchase_detail AS
SELECT p.purchase_id,
       p.reference,
       p.user_id,
       u.first_name || ' ' || u.last_name AS customer,
       u.email,
       p.showtime_id,
       m.movie_id,
       m.title AS movie_title,
       h.name AS hall_name,
       h.format,
       st.starts_at,
       st.language,
       pm.name AS payment_method,
       p.total,
       p.purchased_at,
       seats.seat_count,
       seats.seat_labels
FROM purchase p
JOIN app_user u ON u.user_id = p.user_id
JOIN showtime st ON st.showtime_id = p.showtime_id
JOIN movie m ON m.movie_id = st.movie_id
JOIN hall h ON h.hall_id = st.hall_id
JOIN payment_method pm ON pm.payment_method_id = p.payment_method_id
JOIN LATERAL (
    SELECT count(*)::integer AS seat_count,
           string_agg(s.row_label || s.seat_number, ', ' ORDER BY s.row_label, s.seat_number) AS seat_labels
    FROM ticket t JOIN seat s ON s.seat_id = t.seat_id
    WHERE t.purchase_id = p.purchase_id
) seats ON true;

-- Daily revenue, used by the dashboard chart.
CREATE VIEW v_daily_revenue AS
SELECT (p.purchased_at AT TIME ZONE 'America/Bogota')::date AS sale_date,
       count(DISTINCT p.purchase_id)::integer AS purchases,
       sum(p.total) AS revenue
FROM purchase p
GROUP BY 1;
