-- 005 · Failed login attempts (server-side brute-force throttle)
--
-- One row per failed attempt. The application locks an e-mail after
-- 5 failures and a client IP after 20 failures within 15 minutes, so the
-- limit cannot be bypassed by dropping the session cookie.

CREATE TABLE login_attempt (
    login_attempt_id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    email            varchar(120) NOT NULL,
    ip_address       varchar(45)  NOT NULL,
    attempted_at     timestamptz  NOT NULL DEFAULT now(),
    CONSTRAINT login_attempt_email_lower_ck CHECK (email = lower(email))
);

CREATE INDEX login_attempt_email_idx ON login_attempt (email, attempted_at DESC);
CREATE INDEX login_attempt_ip_idx ON login_attempt (ip_address, attempted_at DESC);
