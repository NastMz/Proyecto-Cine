<?php

declare(strict_types=1);

namespace Cine\Tests\Integration;

use PDOException;

/**
 * Database rules enforced by constraints, triggers and stored functions.
 */
final class SchemaTest extends DatabaseTestCase
{
    public function testAllMigrationsAndSeedsAreRecorded(): void
    {
        self::assertSame(5, (int) $this->scalar('SELECT count(*) FROM schema_migration'));
        self::assertSame(2, (int) $this->scalar('SELECT count(*) FROM schema_seed'));
    }

    public function testExpectedViewsExist(): void
    {
        $views = $this->pdo->query("SELECT viewname FROM pg_views WHERE schemaname = 'public' ORDER BY viewname")
            ->fetchAll(\PDO::FETCH_COLUMN);

        self::assertSame(
            ['v_daily_revenue', 'v_movie_sales', 'v_purchase_detail', 'v_showtime_availability', 'v_user_detail'],
            $views,
        );
    }

    public function testCreatingAHallGeneratesItsSeats(): void
    {
        $hallId = (int) $this->scalar(
            "INSERT INTO hall (name, format, seat_rows, seats_per_row, base_price)
             VALUES ('Sala Prueba', '2D', 3, 4, 10000) RETURNING hall_id",
        );

        self::assertSame(12, (int) $this->scalar('SELECT count(*) FROM seat WHERE hall_id = :h', ['h' => $hallId]));
        self::assertSame(4, (int) $this->scalar("SELECT count(*) FROM seat WHERE hall_id = :h AND seat_kind = 'preferential'", ['h' => $hallId]));
        self::assertSame(2, (int) $this->scalar("SELECT count(*) FROM seat WHERE hall_id = :h AND seat_kind = 'accessible'", ['h' => $hallId]));
        self::assertSame('C4', $this->scalar("SELECT max(row_label || seat_number) FROM seat WHERE hall_id = :h", ['h' => $hallId]));
    }

    public function testChangingTheLayoutRegeneratesSeatsWhileNothingIsSold(): void
    {
        $hallId = (int) $this->scalar(
            "INSERT INTO hall (name, seat_rows, seats_per_row, base_price) VALUES ('Sala Cambio', 2, 2, 1) RETURNING hall_id",
        );
        $this->pdo->exec("UPDATE hall SET seat_rows = 5 WHERE hall_id = {$hallId}");

        self::assertSame(10, (int) $this->scalar('SELECT count(*) FROM seat WHERE hall_id = :h', ['h' => $hallId]));
    }

    public function testLayoutOfAHallWithSoldTicketsCannotChange(): void
    {
        $hallId = (int) $this->scalar('SELECT s.hall_id FROM ticket t JOIN seat s ON s.seat_id = t.seat_id LIMIT 1');

        $state = $this->failingState("UPDATE hall SET seat_rows = seat_rows + 1 WHERE hall_id = {$hallId}");

        self::assertSame('CN005', $state);
    }

    public function testShowtimeEndIsMovieDurationPlusCleaning(): void
    {
        $minutes = $this->scalar(
            "SELECT EXTRACT(EPOCH FROM (st.ends_at - st.starts_at)) / 60 - m.duration_min
             FROM showtime st JOIN movie m ON m.movie_id = st.movie_id LIMIT 1",
        );

        self::assertSame(20, (int) $minutes);
    }

    public function testOverlappingShowtimesInTheSameHallAreRejected(): void
    {
        $existing = $this->pdo->query('SELECT movie_id, hall_id, starts_at FROM showtime ORDER BY showtime_id LIMIT 1')->fetch();

        $state = $this->failingState(sprintf(
            "INSERT INTO showtime (movie_id, hall_id, starts_at, ends_at) VALUES (%d, %d, '%s'::timestamptz + interval '30 minutes', now())",
            $existing['movie_id'],
            $existing['hall_id'],
            $existing['starts_at'],
        ));

        self::assertSame('23P01', $state);
    }

    public function testTicketPriceAppliesTheUserTypeDiscount(): void
    {
        $showtime = $this->freeFutureShowtime();
        $base = (float) $this->scalar('SELECT fn_ticket_price(:s, NULL)', ['s' => $showtime['showtime_id']]);
        $student = (float) $this->scalar('SELECT fn_ticket_price(:s, :u)', [
            's' => $showtime['showtime_id'],
            'u' => $this->userId('laura@butaca.test'),
        ]);

        self::assertGreaterThan(0, $base);
        self::assertSame(round($base * 0.8), $student);
    }

    public function testEmailIsUniqueIgnoringCase(): void
    {
        $state = $this->failingState(
            "INSERT INTO app_user (document, first_name, last_name, email, password_hash, role_id, user_type_id)
             SELECT '99999999', 'X', 'Y', 'ADMIN@BUTACA.TEST', 'x', role_id, user_type_id FROM app_user LIMIT 1",
        );

        self::assertSame('23505', $state);
    }

    public function testCheckConstraintsRejectInvalidValues(): void
    {
        self::assertSame('23514', $this->failingState("INSERT INTO user_type (name, discount_pct) VALUES ('Mal', 150)"));
        self::assertSame('23514', $this->failingState(
            "INSERT INTO movie (title, synopsis, genre, duration_min, rating, release_date) VALUES ('X', 'y', 'z', 90, 'R', CURRENT_DATE)",
        ));
    }

    /**
     * Runs a statement expected to fail inside a savepoint and returns its SQLSTATE.
     */
    private function failingState(string $sql): ?string
    {
        $this->pdo->exec('SAVEPOINT expect_failure');
        try {
            $this->pdo->exec($sql);
        } catch (PDOException $e) {
            $this->pdo->exec('ROLLBACK TO SAVEPOINT expect_failure');

            return $e->errorInfo[0] ?? null;
        }
        $this->pdo->exec('RELEASE SAVEPOINT expect_failure');

        return null;
    }
}
