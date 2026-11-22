-- Seed 001 · Reference and demo catalog data.
-- All people, movies and companies below are fictional.
--
-- Demo accounts (local development only):
--   admin@butaca.test     / Admin.2026     (Administrador)
--   taquilla@butaca.test  / Taquilla.2026  (Taquilla)
--   laura@butaca.test     / Cliente.2026   (Cliente, Estudiante)
--   andres@butaca.test    / Cliente.2026   (Cliente, General)
--   marta@butaca.test     / Cliente.2026   (Cliente, Adulto mayor)

INSERT INTO role (code, name, description) VALUES
    ('admin',    'Administrador', 'Gestiona todo el sistema'),
    ('taquilla', 'Taquilla',      'Consulta ventas y funciones'),
    ('cliente',  'Cliente',       'Compra boletos en línea');

INSERT INTO permission (code, description) VALUES
    ('admin.access',     'Ingresar al panel de administración'),
    ('catalog.manage',   'Gestionar películas, salas y funciones'),
    ('users.manage',     'Gestionar usuarios, roles y permisos'),
    ('sales.view',       'Consultar compras y boletos'),
    ('tickets.buy',      'Comprar boletos');

INSERT INTO role_permission (role_id, permission_id)
SELECT r.role_id, p.permission_id
FROM role r CROSS JOIN permission p
WHERE r.code = 'admin'
   OR (r.code = 'taquilla' AND p.code IN ('admin.access', 'sales.view', 'tickets.buy'))
   OR (r.code = 'cliente'  AND p.code = 'tickets.buy');

INSERT INTO user_type (name, discount_pct) VALUES
    ('General', 0),
    ('Estudiante', 20),
    ('Adulto mayor', 30);

INSERT INTO app_user (document, first_name, last_name, email, phone, password_hash, role_id, user_type_id)
SELECT v.document, v.first_name, v.last_name, v.email, v.phone, v.password_hash,
       (SELECT role_id FROM role WHERE code = v.role_code),
       (SELECT user_type_id FROM user_type WHERE name = v.user_type)
FROM (VALUES
    ('1000000001', 'Ana',    'Rivera', 'admin@butaca.test',    '300 000 0001',
     '$2y$12$k0Z9oMeYrK33ekAapfJI3uYSucpN3u2Aa9Ufm.4kXbeMw4XxeMWyW', 'admin',    'General'),
    ('1000000002', 'Tomás',  'Peña',   'taquilla@butaca.test', '300 000 0002',
     '$2y$12$Vs2ByElCT8lt3S6xxhqN9OzLarsQ0wjZKRiz9x9zkbIbOr9VA40lO', 'taquilla', 'General'),
    ('1000000003', 'Laura',  'Gómez',  'laura@butaca.test',    '300 000 0003',
     '$2y$12$0oVPLYIrxclijvsobrK89OBfT65UhFLTPik2rB.68Z9ZwmqTk3gYa', 'cliente',  'Estudiante'),
    ('1000000004', 'Andrés', 'Mejía',  'andres@butaca.test',   '300 000 0004',
     '$2y$12$0oVPLYIrxclijvsobrK89OBfT65UhFLTPik2rB.68Z9ZwmqTk3gYa', 'cliente',  'General'),
    ('1000000005', 'Marta',  'Ortiz',  'marta@butaca.test',    '300 000 0005',
     '$2y$12$0oVPLYIrxclijvsobrK89OBfT65UhFLTPik2rB.68Z9ZwmqTk3gYa', 'cliente',  'Adulto mayor')
) AS v(document, first_name, last_name, email, phone, password_hash, role_code, user_type);

INSERT INTO payment_method (name) VALUES
    ('Tarjeta de crédito'),
    ('Tarjeta débito'),
    ('Billetera digital'),
    ('Efectivo en taquilla');

INSERT INTO director (full_name, nationality, birth_date) VALUES
    ('Valeria Castaño',  'Colombiana', '1979-04-12'),
    ('Iñaki Ferrer',     'Española',   '1968-09-30'),
    ('Rosa Quintero',    'Mexicana',   '1985-01-22'),
    ('Joaquín Salas',    'Chilena',    '1972-06-05'),
    ('Mariana Bustos',   'Argentina',  '1990-11-17');

INSERT INTO actor (full_name, nationality, birth_date) VALUES
    ('Camila Restrepo',  'Colombiana', '1992-03-08'),
    ('Diego Arango',     'Colombiano', '1986-07-19'),
    ('Lucía Ventura',    'Española',   '1995-12-01'),
    ('Mateo Lozano',     'Mexicano',   '1981-02-14'),
    ('Sofía Herrera',    'Chilena',    '1999-05-27'),
    ('Julián Pardo',     'Argentino',  '1975-10-09'),
    ('Elena Duarte',     'Peruana',    '1988-08-03'),
    ('Samuel Ríos',      'Colombiano', '2003-01-30');

INSERT INTO movie (title, original_title, synopsis, genre, duration_min, rating, release_date, poster_hue) VALUES
    ('La última función',  NULL,
     'Un proyeccionista descubre que el viejo cine de su pueblo cerrará y decide organizar una última noche que nadie olvidará.',
     'Drama', 118, '7+', CURRENT_DATE - 20, 12),
    ('Órbita silenciosa',  'Silent Orbit',
     'Tres astronautas pierden contacto con la Tierra y deben decidir en quién confiar mientras se agota el oxígeno.',
     'Ciencia ficción', 134, '12+', CURRENT_DATE - 12, 215),
    ('Café para dos',      NULL,
     'Dos baristas rivales en Medellín se ven obligados a compartir local durante una temporada de lluvias.',
     'Comedia romántica', 102, 'T', CURRENT_DATE - 30, 28),
    ('El eco de la sierra', NULL,
     'Una guía de montaña y un periodista siguen el rastro de una expedición desaparecida hace cuarenta años.',
     'Aventura', 127, '12+', CURRENT_DATE - 5, 140),
    ('Medianoche en el archivo', NULL,
     'Una archivista encuentra en una bobina olvidada la prueba de un crimen que todos creían resuelto.',
     'Suspenso', 111, '15+', CURRENT_DATE - 8, 275),
    ('Pequeños gigantes',  NULL,
     'Un equipo de fútbol infantil de barrio llega por sorpresa al torneo nacional.',
     'Familiar', 95, 'T', CURRENT_DATE - 15, 48),
    ('Ciudad de neón',     'Neon City',
     'En una metrópolis que nunca duerme, una detective sigue la pista de un algoritmo que predice delitos.',
     'Acción', 142, '15+', CURRENT_DATE - 2, 305),
    ('Las cartas de Amalia', NULL,
     'Tras la muerte de su abuela, una joven lee las cartas que revelan un amor prohibido en los años cincuenta.',
     'Drama', 109, '12+', CURRENT_DATE - 40, 340);

INSERT INTO movie_director (movie_id, director_id)
SELECT m.movie_id, d.director_id
FROM (VALUES
    ('La última función',        'Valeria Castaño'),
    ('Órbita silenciosa',        'Iñaki Ferrer'),
    ('Café para dos',            'Rosa Quintero'),
    ('El eco de la sierra',      'Joaquín Salas'),
    ('El eco de la sierra',      'Mariana Bustos'),
    ('Medianoche en el archivo', 'Mariana Bustos'),
    ('Pequeños gigantes',        'Valeria Castaño'),
    ('Ciudad de neón',           'Iñaki Ferrer'),
    ('Las cartas de Amalia',     'Rosa Quintero')
) AS v(title, director)
JOIN movie m ON m.title = v.title
JOIN director d ON d.full_name = v.director;

INSERT INTO movie_cast (movie_id, actor_id, character_name, billing_order)
SELECT m.movie_id, a.actor_id, v.character_name, v.billing_order
FROM (VALUES
    ('La última función',        'Diego Arango',    'Ernesto',            1),
    ('La última función',        'Camila Restrepo', 'Inés',               2),
    ('La última función',        'Samuel Ríos',     'Nico',               3),
    ('Órbita silenciosa',        'Lucía Ventura',   'Comandante Vega',    1),
    ('Órbita silenciosa',        'Mateo Lozano',    'Dr. Ibarra',         2),
    ('Órbita silenciosa',        'Julián Pardo',    'Control de misión',  3),
    ('Café para dos',            'Camila Restrepo', 'Paula',              1),
    ('Café para dos',            'Diego Arango',    'Simón',              2),
    ('El eco de la sierra',      'Elena Duarte',    'Wara',               1),
    ('El eco de la sierra',      'Julián Pardo',    'Federico',           2),
    ('Medianoche en el archivo', 'Sofía Herrera',   'Clara',              1),
    ('Medianoche en el archivo', 'Mateo Lozano',    'Inspector Ruiz',     2),
    ('Pequeños gigantes',        'Samuel Ríos',     'Capitán Toño',       1),
    ('Pequeños gigantes',        'Elena Duarte',    'Profesora Gladys',   2),
    ('Ciudad de neón',           'Lucía Ventura',   'Detective Aria',     1),
    ('Ciudad de neón',           'Diego Arango',    'Kael',               2),
    ('Ciudad de neón',           'Sofía Herrera',   'La voz',             3),
    ('Las cartas de Amalia',     'Camila Restrepo', 'Amalia (joven)',     1),
    ('Las cartas de Amalia',     'Elena Duarte',    'Amalia',             2),
    ('Las cartas de Amalia',     'Sofía Herrera',   'Valentina',          3)
) AS v(title, actor, character_name, billing_order)
JOIN movie m ON m.title = v.title
JOIN actor a ON a.full_name = v.actor;

-- Seats are generated automatically by trg_hall_sync_seats.
INSERT INTO hall (name, format, seat_rows, seats_per_row, base_price) VALUES
    ('Sala 1',      '2D',    8, 12, 14000),
    ('Sala 2',      '3D',    7, 10, 19000),
    ('Sala Gran',   'IMAX', 10, 14, 26000),
    ('Sala Motion', '4DX',   6,  8, 32000);
