<?php

declare(strict_types=1);

namespace Cine\Admin;

/**
 * All tables manageable from the admin panel, grouped like the sidebar.
 */
final class ResourceRegistry
{
    /** @var array<string, Resource> */
    private array $resources = [];

    public function __construct()
    {
        foreach (self::definitions() as $resource) {
            $this->resources[$resource->slug] = $resource;
        }
    }

    public function get(string $slug): ?Resource
    {
        return $this->resources[$slug] ?? null;
    }

    /**
     * @return array<string, list<Resource>> group => resources
     */
    public function grouped(): array
    {
        $groups = [];
        foreach ($this->resources as $resource) {
            $groups[$resource->group][] = $resource;
        }

        return $groups;
    }

    /**
     * @return list<Resource>
     */
    public function all(): array
    {
        return array_values($this->resources);
    }

    /**
     * @return list<Resource>
     */
    private static function definitions(): array
    {
        $ratings = ['T' => 'T · Todo público', '7+' => '7+', '12+' => '12+', '15+' => '15+', '18+' => '18+'];
        $formats = ['2D' => '2D', '3D' => '3D', 'IMAX' => 'IMAX', '4DX' => '4DX'];
        $languages = ['Doblada' => 'Doblada', 'Subtitulada' => 'Subtitulada', 'Original' => 'Original'];
        $movies = 'SELECT movie_id, title FROM movie ORDER BY title';
        $people = static fn (string $table): string => "SELECT {$table}_id, full_name FROM {$table} ORDER BY full_name";

        return [
            // --- Películas -------------------------------------------------
            new Resource(
                slug: 'peliculas',
                title: 'Películas',
                singular: 'película',
                group: 'Películas',
                permission: 'catalog.manage',
                table: 'movie',
                keys: ['movie_id'],
                listSource: 'movie',
                columns: [
                    'movie_id' => ['ID'],
                    'title' => ['Título'],
                    'genre' => ['Género'],
                    'duration_min' => ['Duración', 'minutes'],
                    'rating' => ['Clasif.'],
                    'release_date' => ['Estreno', 'date'],
                    'is_active' => ['Activa', 'bool'],
                ],
                searchable: ['title', 'genre', 'original_title'],
                defaultSort: 'release_date',
                fields: [
                    Field::text('title', 'Título', 120),
                    Field::text('original_title', 'Título original', 120, false),
                    Field::textarea('synopsis', 'Sinopsis', 2000),
                    Field::text('genre', 'Género', 40),
                    Field::int('duration_min', 'Duración (min)', 1, 400),
                    Field::choice('rating', 'Clasificación', $ratings),
                    Field::date('release_date', 'Fecha de estreno'),
                    Field::int('poster_hue', 'Tono del afiche (0-359)', 0, 359, help: 'Color base del afiche generado.'),
                    Field::bool('is_active', 'Activa en cartelera'),
                ],
            ),
            new Resource(
                slug: 'directores',
                title: 'Directores',
                singular: 'director',
                group: 'Películas',
                permission: 'catalog.manage',
                table: 'director',
                keys: ['director_id'],
                listSource: 'director',
                columns: [
                    'director_id' => ['ID'],
                    'full_name' => ['Nombre'],
                    'nationality' => ['Nacionalidad'],
                    'birth_date' => ['Nacimiento', 'date'],
                ],
                searchable: ['full_name', 'nationality'],
                defaultSort: 'full_name',
                fields: [
                    Field::text('full_name', 'Nombre completo', 100),
                    Field::text('nationality', 'Nacionalidad', 60, false),
                    Field::date('birth_date', 'Fecha de nacimiento', false),
                ],
            ),
            new Resource(
                slug: 'dirige',
                title: 'Dirige',
                singular: 'asignación de director',
                group: 'Películas',
                permission: 'catalog.manage',
                table: 'movie_director',
                keys: ['movie_id', 'director_id'],
                listSource: '(SELECT md.movie_id, md.director_id, m.title, d.full_name
                              FROM movie_director md
                              JOIN movie m ON m.movie_id = md.movie_id
                              JOIN director d ON d.director_id = md.director_id) AS src',
                columns: ['title' => ['Película'], 'full_name' => ['Director']],
                searchable: ['title', 'full_name'],
                defaultSort: 'title',
                fields: [
                    Field::relation('movie_id', 'Película', $movies),
                    Field::relation('director_id', 'Director', $people('director')),
                ],
                editable: false,
                description: 'Relación muchos a muchos entre películas y directores.',
            ),
            new Resource(
                slug: 'actores',
                title: 'Actores',
                singular: 'actor',
                group: 'Películas',
                permission: 'catalog.manage',
                table: 'actor',
                keys: ['actor_id'],
                listSource: 'actor',
                columns: [
                    'actor_id' => ['ID'],
                    'full_name' => ['Nombre'],
                    'nationality' => ['Nacionalidad'],
                    'birth_date' => ['Nacimiento', 'date'],
                ],
                searchable: ['full_name', 'nationality'],
                defaultSort: 'full_name',
                fields: [
                    Field::text('full_name', 'Nombre completo', 100),
                    Field::text('nationality', 'Nacionalidad', 60, false),
                    Field::date('birth_date', 'Fecha de nacimiento', false),
                ],
            ),
            new Resource(
                slug: 'reparto',
                title: 'Reparto',
                singular: 'papel',
                group: 'Películas',
                permission: 'catalog.manage',
                table: 'movie_cast',
                keys: ['cast_id'],
                listSource: '(SELECT mc.cast_id, m.title, a.full_name, mc.character_name, mc.billing_order
                              FROM movie_cast mc
                              JOIN movie m ON m.movie_id = mc.movie_id
                              JOIN actor a ON a.actor_id = mc.actor_id) AS src',
                columns: [
                    'title' => ['Película'],
                    'full_name' => ['Actor'],
                    'character_name' => ['Papel'],
                    'billing_order' => ['Orden'],
                ],
                searchable: ['title', 'full_name', 'character_name'],
                defaultSort: 'title',
                fields: [
                    Field::relation('movie_id', 'Película', $movies),
                    Field::relation('actor_id', 'Actor', $people('actor')),
                    Field::text('character_name', 'Personaje', 80),
                    Field::int('billing_order', 'Orden en créditos', 1, 99),
                ],
            ),

            // --- Salas ------------------------------------------------------
            new Resource(
                slug: 'salas',
                title: 'Salas',
                singular: 'sala',
                group: 'Salas',
                permission: 'catalog.manage',
                table: 'hall',
                keys: ['hall_id'],
                listSource: '(SELECT h.*, h.seat_rows * h.seats_per_row AS capacity FROM hall h) AS src',
                columns: [
                    'hall_id' => ['ID'],
                    'name' => ['Nombre'],
                    'format' => ['Formato'],
                    'capacity' => ['Sillas'],
                    'base_price' => ['Precio base', 'money'],
                    'is_active' => ['Activa', 'bool'],
                ],
                searchable: ['name', 'format'],
                defaultSort: 'name',
                fields: [
                    Field::text('name', 'Nombre', 40),
                    Field::choice('format', 'Formato', $formats),
                    Field::int('seat_rows', 'Filas', 1, 26, help: 'Las sillas se generan automáticamente (filas A-Z).'),
                    Field::int('seats_per_row', 'Sillas por fila', 1, 40),
                    Field::decimal('base_price', 'Precio base', 0, 1000000),
                    Field::bool('is_active', 'Activa'),
                ],
            ),
            new Resource(
                slug: 'funciones',
                title: 'Funciones',
                singular: 'función',
                group: 'Salas',
                permission: 'catalog.manage',
                table: 'showtime',
                keys: ['showtime_id'],
                listSource: 'v_showtime_availability',
                columns: [
                    'showtime_id' => ['ID'],
                    'movie_title' => ['Película'],
                    'hall_name' => ['Sala'],
                    'starts_at' => ['Inicio', 'datetime'],
                    'language' => ['Idioma'],
                    'price' => ['Precio', 'money'],
                    'sold' => ['Vendidas'],
                    'occupancy_pct' => ['Ocupación', 'percent'],
                ],
                searchable: ['movie_title', 'hall_name', 'language'],
                defaultSort: 'starts_at',
                fields: [
                    Field::relation('movie_id', 'Película', $movies),
                    Field::relation('hall_id', 'Sala', 'SELECT hall_id, name || \' · \' || format FROM hall ORDER BY name'),
                    Field::dateTime('starts_at', 'Inicio'),
                    Field::choice('language', 'Idioma', $languages),
                    Field::decimal('price_override', 'Precio especial', 0, 1000000, false, 'Opcional: reemplaza el precio base de la sala.'),
                ],
                defaultDir: 'desc',
                description: 'La hora de fin se calcula en la base de datos (duración + 20 min de aseo) y dos funciones no pueden cruzarse en la misma sala.',
            ),
            new Resource(
                slug: 'sillas',
                title: 'Sillas',
                singular: 'silla',
                group: 'Salas',
                permission: 'catalog.manage',
                table: 'seat',
                keys: ['seat_id'],
                listSource: '(SELECT s.seat_id, h.name AS hall_name, s.row_label, s.seat_number,
                                     s.row_label || s.seat_number AS label, s.seat_kind
                              FROM seat s JOIN hall h ON h.hall_id = s.hall_id) AS src',
                columns: [
                    'hall_name' => ['Sala'],
                    'label' => ['Silla'],
                    'seat_kind' => ['Tipo', 'seat_kind'],
                ],
                searchable: ['hall_name', 'label', 'seat_kind'],
                defaultSort: 'hall_name',
                description: 'Generadas por el disparador trg_hall_sync_seats a partir de la distribución de cada sala.',
            ),

            // --- Ventas -----------------------------------------------------
            new Resource(
                slug: 'compras',
                title: 'Compras',
                singular: 'compra',
                group: 'Ventas',
                permission: 'sales.view',
                table: 'purchase',
                keys: ['purchase_id'],
                listSource: 'v_purchase_detail',
                columns: [
                    'purchase_id' => ['#'],
                    'customer' => ['Cliente'],
                    'movie_title' => ['Película'],
                    'starts_at' => ['Función', 'datetime'],
                    'seat_labels' => ['Sillas'],
                    'payment_method' => ['Pago'],
                    'total' => ['Total', 'money'],
                ],
                searchable: ['customer', 'movie_title', 'payment_method', 'seat_labels'],
                defaultSort: 'purchase_id',
                defaultDir: 'desc',
            ),
            new Resource(
                slug: 'boletos',
                title: 'Boletos',
                singular: 'boleto',
                group: 'Ventas',
                permission: 'sales.view',
                table: 'ticket',
                keys: ['ticket_id'],
                listSource: '(SELECT t.ticket_id, t.purchase_id, m.title, h.name AS hall_name, st.starts_at,
                                     s.row_label || s.seat_number AS seat, t.unit_price
                              FROM ticket t
                              JOIN showtime st ON st.showtime_id = t.showtime_id
                              JOIN movie m ON m.movie_id = st.movie_id
                              JOIN hall h ON h.hall_id = st.hall_id
                              JOIN seat s ON s.seat_id = t.seat_id) AS src',
                columns: [
                    'ticket_id' => ['#'],
                    'purchase_id' => ['Compra'],
                    'title' => ['Película'],
                    'hall_name' => ['Sala'],
                    'starts_at' => ['Función', 'datetime'],
                    'seat' => ['Silla'],
                    'unit_price' => ['Valor', 'money'],
                ],
                searchable: ['title', 'hall_name', 'seat'],
                defaultSort: 'ticket_id',
                defaultDir: 'desc',
            ),
            new Resource(
                slug: 'medios-pago',
                title: 'Medios de pago',
                singular: 'medio de pago',
                group: 'Ventas',
                permission: 'catalog.manage',
                table: 'payment_method',
                keys: ['payment_method_id'],
                listSource: 'payment_method',
                columns: [
                    'payment_method_id' => ['ID'],
                    'name' => ['Nombre'],
                    'is_active' => ['Activo', 'bool'],
                ],
                searchable: ['name'],
                defaultSort: 'name',
                fields: [
                    Field::text('name', 'Nombre', 40),
                    Field::bool('is_active', 'Activo'),
                ],
            ),

            // --- Usuarios ---------------------------------------------------
            new Resource(
                slug: 'usuarios',
                title: 'Usuarios',
                singular: 'usuario',
                group: 'Usuarios',
                permission: 'users.manage',
                table: 'app_user',
                keys: ['user_id'],
                listSource: 'v_user_detail',
                columns: [
                    'user_id' => ['ID'],
                    'full_name' => ['Nombre'],
                    'document' => ['Documento'],
                    'email' => ['Correo'],
                    'role_name' => ['Rol'],
                    'user_type_name' => ['Tipo'],
                    'is_active' => ['Activo', 'bool'],
                ],
                searchable: ['full_name', 'document', 'email', 'role_name'],
                defaultSort: 'user_id',
                fields: [
                    Field::text('document', 'Documento', 20, true, '^[0-9]{5,20}$', 'Solo números, entre 5 y 20 dígitos.'),
                    Field::text('first_name', 'Nombres', 60),
                    Field::text('last_name', 'Apellidos', 60),
                    Field::email('email', 'Correo electrónico'),
                    Field::text('phone', 'Teléfono', 20, false, '^\+?[0-9 ]{7,20}$'),
                    Field::password('password_hash', 'Contraseña'),
                    Field::relation('role_id', 'Rol', 'SELECT role_id, name FROM role ORDER BY name'),
                    Field::relation('user_type_id', 'Tipo de usuario', 'SELECT user_type_id, name FROM user_type ORDER BY name'),
                    Field::bool('is_active', 'Activo'),
                ],
            ),
            new Resource(
                slug: 'tipos-usuario',
                title: 'Tipos de usuario',
                singular: 'tipo de usuario',
                group: 'Usuarios',
                permission: 'users.manage',
                table: 'user_type',
                keys: ['user_type_id'],
                listSource: 'user_type',
                columns: [
                    'user_type_id' => ['ID'],
                    'name' => ['Nombre'],
                    'discount_pct' => ['Descuento', 'percent'],
                ],
                searchable: ['name'],
                defaultSort: 'user_type_id',
                fields: [
                    Field::text('name', 'Nombre', 60),
                    Field::decimal('discount_pct', 'Descuento (%)', 0, 100, help: 'Se aplica al precio de cada boleto.'),
                ],
            ),
            new Resource(
                slug: 'roles',
                title: 'Roles',
                singular: 'rol',
                group: 'Usuarios',
                permission: 'users.manage',
                table: 'role',
                keys: ['role_id'],
                listSource: 'role',
                columns: [
                    'role_id' => ['ID'],
                    'code' => ['Código'],
                    'name' => ['Nombre'],
                    'description' => ['Descripción'],
                    'is_active' => ['Activo', 'bool'],
                ],
                searchable: ['code', 'name', 'description'],
                defaultSort: 'role_id',
                fields: [
                    Field::text('code', 'Código', 30, true, '^[a-z][a-z_]*$', 'Minúsculas y guion bajo, p. ej. "taquilla".'),
                    Field::text('name', 'Nombre', 60),
                    Field::text('description', 'Descripción', 255, false),
                    Field::bool('is_active', 'Activo'),
                ],
            ),
            new Resource(
                slug: 'permisos',
                title: 'Permisos',
                singular: 'permiso',
                group: 'Usuarios',
                permission: 'users.manage',
                table: 'permission',
                keys: ['permission_id'],
                listSource: 'permission',
                columns: [
                    'permission_id' => ['ID'],
                    'code' => ['Código'],
                    'description' => ['Descripción'],
                ],
                searchable: ['code', 'description'],
                defaultSort: 'code',
                fields: [
                    Field::text('code', 'Código', 60, true, '^[a-z]+\.[a-z_]+$', 'Formato modulo.accion, p. ej. "sales.view".'),
                    Field::text('description', 'Descripción', 160),
                ],
            ),
            new Resource(
                slug: 'permisos-rol',
                title: 'Permisos por rol',
                singular: 'permiso de rol',
                group: 'Usuarios',
                permission: 'users.manage',
                table: 'role_permission',
                keys: ['role_id', 'permission_id'],
                listSource: '(SELECT rp.role_id, rp.permission_id, r.name AS role_name, p.code, p.description
                              FROM role_permission rp
                              JOIN role r ON r.role_id = rp.role_id
                              JOIN permission p ON p.permission_id = rp.permission_id) AS src',
                columns: ['role_name' => ['Rol'], 'code' => ['Permiso'], 'description' => ['Descripción']],
                searchable: ['role_name', 'code', 'description'],
                defaultSort: 'role_name',
                fields: [
                    Field::relation('role_id', 'Rol', 'SELECT role_id, name FROM role ORDER BY name'),
                    Field::relation('permission_id', 'Permiso', 'SELECT permission_id, code FROM permission ORDER BY code'),
                ],
                editable: false,
            ),
        ];
    }
}
