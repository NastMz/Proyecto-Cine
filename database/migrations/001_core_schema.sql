-- 001 · Core relational schema
-- Users and access control, catalog (movies, directors, actors), cinema
-- infrastructure (halls, seats, showtimes) and sales (purchases, tickets).

CREATE EXTENSION IF NOT EXISTS btree_gist;

-- ---------------------------------------------------------------------------
-- Access control
-- ---------------------------------------------------------------------------

CREATE TABLE role (
    role_id     smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    code        varchar(30)  NOT NULL,
    name        varchar(60)  NOT NULL,
    description varchar(255),
    is_active   boolean      NOT NULL DEFAULT true,
    CONSTRAINT role_code_uq UNIQUE (code),
    CONSTRAINT role_code_format_ck CHECK (code ~ '^[a-z][a-z_]*$')
);

CREATE TABLE permission (
    permission_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    code          varchar(60)  NOT NULL,
    description   varchar(160) NOT NULL,
    CONSTRAINT permission_code_uq UNIQUE (code),
    CONSTRAINT permission_code_format_ck CHECK (code ~ '^[a-z]+\.[a-z_]+$')
);

-- Many-to-many: which permissions each role grants.
CREATE TABLE role_permission (
    role_id       smallint NOT NULL REFERENCES role (role_id) ON DELETE CASCADE,
    permission_id smallint NOT NULL REFERENCES permission (permission_id) ON DELETE CASCADE,
    PRIMARY KEY (role_id, permission_id)
);
CREATE INDEX role_permission_permission_idx ON role_permission (permission_id);

-- Customer category ("tipo de usuario"); drives the ticket discount.
CREATE TABLE user_type (
    user_type_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name         varchar(60)  NOT NULL,
    discount_pct numeric(5, 2) NOT NULL DEFAULT 0,
    CONSTRAINT user_type_name_uq UNIQUE (name),
    CONSTRAINT user_type_discount_ck CHECK (discount_pct BETWEEN 0 AND 100)
);

CREATE TABLE app_user (
    user_id       integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    document      varchar(20)  NOT NULL,
    first_name    varchar(60)  NOT NULL,
    last_name     varchar(60)  NOT NULL,
    email         varchar(120) NOT NULL,
    phone         varchar(20),
    password_hash varchar(255) NOT NULL,
    role_id       smallint     NOT NULL REFERENCES role (role_id) ON DELETE RESTRICT,
    user_type_id  smallint     NOT NULL REFERENCES user_type (user_type_id) ON DELETE RESTRICT,
    is_active     boolean      NOT NULL DEFAULT true,
    created_at    timestamptz  NOT NULL DEFAULT now(),
    updated_at    timestamptz  NOT NULL DEFAULT now(),
    CONSTRAINT app_user_document_uq UNIQUE (document),
    CONSTRAINT app_user_document_ck CHECK (document ~ '^[0-9]{5,20}$'),
    CONSTRAINT app_user_email_ck CHECK (email ~* '^[^@[:space:]]+@[^@[:space:]]+\.[^@[:space:]]+$'),
    CONSTRAINT app_user_phone_ck CHECK (phone IS NULL OR phone ~ '^\+?[0-9 ]{7,20}$')
);
-- E-mails are unique regardless of letter case.
CREATE UNIQUE INDEX app_user_email_lower_uq ON app_user (lower(email));
CREATE INDEX app_user_role_idx ON app_user (role_id);
CREATE INDEX app_user_user_type_idx ON app_user (user_type_id);

-- ---------------------------------------------------------------------------
-- Catalog
-- ---------------------------------------------------------------------------

CREATE TABLE director (
    director_id integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    full_name   varchar(100) NOT NULL,
    nationality varchar(60),
    birth_date  date,
    CONSTRAINT director_birth_ck CHECK (birth_date IS NULL OR birth_date > DATE '1850-01-01')
);

CREATE TABLE actor (
    actor_id    integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    full_name   varchar(100) NOT NULL,
    nationality varchar(60),
    birth_date  date,
    CONSTRAINT actor_birth_ck CHECK (birth_date IS NULL OR birth_date > DATE '1850-01-01')
);

CREATE TABLE movie (
    movie_id       integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title          varchar(120) NOT NULL,
    original_title varchar(120),
    synopsis       text         NOT NULL,
    genre          varchar(40)  NOT NULL,
    duration_min   smallint     NOT NULL,
    rating         varchar(4)   NOT NULL,
    release_date   date         NOT NULL,
    poster_hue     smallint     NOT NULL DEFAULT 350,
    is_active      boolean      NOT NULL DEFAULT true,
    created_at     timestamptz  NOT NULL DEFAULT now(),
    updated_at     timestamptz  NOT NULL DEFAULT now(),
    CONSTRAINT movie_title_release_uq UNIQUE (title, release_date),
    CONSTRAINT movie_duration_ck CHECK (duration_min BETWEEN 1 AND 400),
    CONSTRAINT movie_rating_ck CHECK (rating IN ('T', '7+', '12+', '15+', '18+')),
    CONSTRAINT movie_poster_hue_ck CHECK (poster_hue BETWEEN 0 AND 359)
);
CREATE INDEX movie_release_idx ON movie (release_date DESC);

-- "Dirige": a movie can have several directors and vice versa.
CREATE TABLE movie_director (
    movie_id    integer NOT NULL REFERENCES movie (movie_id) ON DELETE CASCADE,
    director_id integer NOT NULL REFERENCES director (director_id) ON DELETE RESTRICT,
    PRIMARY KEY (movie_id, director_id)
);
CREATE INDEX movie_director_director_idx ON movie_director (director_id);

-- "Reparto" / "Papel": an actor plays one or more characters in a movie.
CREATE TABLE movie_cast (
    cast_id        integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    movie_id       integer     NOT NULL REFERENCES movie (movie_id) ON DELETE CASCADE,
    actor_id       integer     NOT NULL REFERENCES actor (actor_id) ON DELETE RESTRICT,
    character_name varchar(80) NOT NULL,
    billing_order  smallint    NOT NULL DEFAULT 1,
    CONSTRAINT movie_cast_role_uq UNIQUE (movie_id, actor_id, character_name),
    CONSTRAINT movie_cast_billing_ck CHECK (billing_order > 0)
);
CREATE INDEX movie_cast_actor_idx ON movie_cast (actor_id);

-- ---------------------------------------------------------------------------
-- Cinema infrastructure
-- ---------------------------------------------------------------------------

CREATE TABLE hall (
    hall_id       smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name          varchar(40)    NOT NULL,
    format        varchar(5)     NOT NULL DEFAULT '2D',
    seat_rows     smallint       NOT NULL,
    seats_per_row smallint       NOT NULL,
    base_price    numeric(10, 2) NOT NULL,
    is_active     boolean        NOT NULL DEFAULT true,
    CONSTRAINT hall_name_uq UNIQUE (name),
    CONSTRAINT hall_format_ck CHECK (format IN ('2D', '3D', 'IMAX', '4DX')),
    CONSTRAINT hall_rows_ck CHECK (seat_rows BETWEEN 1 AND 26),
    CONSTRAINT hall_seats_per_row_ck CHECK (seats_per_row BETWEEN 1 AND 40),
    CONSTRAINT hall_base_price_ck CHECK (base_price >= 0)
);

-- Seats are generated from the hall layout by a trigger (see 003/004).
CREATE TABLE seat (
    seat_id     integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    hall_id     smallint    NOT NULL REFERENCES hall (hall_id) ON DELETE CASCADE,
    row_label   char(1)     NOT NULL,
    seat_number smallint    NOT NULL,
    seat_kind   varchar(12) NOT NULL DEFAULT 'standard',
    CONSTRAINT seat_position_uq UNIQUE (hall_id, row_label, seat_number),
    CONSTRAINT seat_row_ck CHECK (row_label ~ '^[A-Z]$'),
    CONSTRAINT seat_number_ck CHECK (seat_number > 0),
    CONSTRAINT seat_kind_ck CHECK (seat_kind IN ('standard', 'preferential', 'accessible'))
);

CREATE TABLE showtime (
    showtime_id    integer GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    movie_id       integer        NOT NULL REFERENCES movie (movie_id) ON DELETE RESTRICT,
    hall_id        smallint       NOT NULL REFERENCES hall (hall_id) ON DELETE RESTRICT,
    starts_at      timestamptz    NOT NULL,
    ends_at        timestamptz    NOT NULL, -- filled by trigger from movie duration
    language       varchar(12)    NOT NULL DEFAULT 'Doblada',
    price_override numeric(10, 2),
    CONSTRAINT showtime_time_ck CHECK (ends_at > starts_at),
    CONSTRAINT showtime_language_ck CHECK (language IN ('Doblada', 'Subtitulada', 'Original')),
    CONSTRAINT showtime_price_ck CHECK (price_override IS NULL OR price_override >= 0),
    -- A hall cannot host two showtimes whose time ranges overlap.
    CONSTRAINT showtime_no_overlap_ex EXCLUDE USING gist (
        hall_id WITH =,
        tstzrange(starts_at, ends_at, '[)') WITH &&
    )
);
CREATE INDEX showtime_movie_start_idx ON showtime (movie_id, starts_at);
CREATE INDEX showtime_start_idx ON showtime (starts_at);

-- ---------------------------------------------------------------------------
-- Sales
-- ---------------------------------------------------------------------------

-- "Tipo de pago"
CREATE TABLE payment_method (
    payment_method_id smallint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name              varchar(40) NOT NULL,
    is_active         boolean     NOT NULL DEFAULT true,
    CONSTRAINT payment_method_name_uq UNIQUE (name)
);

-- "Pagado": one paid purchase groups the tickets bought together.
CREATE TABLE purchase (
    purchase_id       bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    reference         uuid           NOT NULL DEFAULT gen_random_uuid(),
    user_id           integer        NOT NULL REFERENCES app_user (user_id) ON DELETE RESTRICT,
    showtime_id       integer        NOT NULL REFERENCES showtime (showtime_id) ON DELETE RESTRICT,
    payment_method_id smallint       NOT NULL REFERENCES payment_method (payment_method_id) ON DELETE RESTRICT,
    total             numeric(12, 2) NOT NULL,
    purchased_at      timestamptz    NOT NULL DEFAULT now(),
    CONSTRAINT purchase_reference_uq UNIQUE (reference),
    CONSTRAINT purchase_total_ck CHECK (total >= 0),
    -- Target of the composite foreign key from ticket.
    CONSTRAINT purchase_showtime_uq UNIQUE (purchase_id, showtime_id)
);
CREATE INDEX purchase_user_idx ON purchase (user_id, purchased_at DESC);
CREATE INDEX purchase_showtime_idx ON purchase (showtime_id);

-- "Boleto": one seat for one showtime. The unique constraint is the final
-- guarantee that a seat is never sold twice for the same showtime.
CREATE TABLE ticket (
    ticket_id   bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    purchase_id bigint         NOT NULL,
    showtime_id integer        NOT NULL,
    seat_id     integer        NOT NULL REFERENCES seat (seat_id) ON DELETE RESTRICT,
    unit_price  numeric(10, 2) NOT NULL,
    CONSTRAINT ticket_seat_showtime_uq UNIQUE (showtime_id, seat_id),
    CONSTRAINT ticket_price_ck CHECK (unit_price >= 0),
    -- The ticket must belong to the same showtime as its purchase.
    CONSTRAINT ticket_purchase_fk FOREIGN KEY (purchase_id, showtime_id)
        REFERENCES purchase (purchase_id, showtime_id) ON DELETE CASCADE
);
CREATE INDEX ticket_purchase_idx ON ticket (purchase_id);
CREATE INDEX ticket_seat_idx ON ticket (seat_id);
