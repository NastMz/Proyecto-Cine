<?php

declare(strict_types=1);

namespace Cine\Tests\Integration;

use Cine\Config;
use Cine\Database\Database;
use Cine\Database\Migrator;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests run against a throw-away PostgreSQL database
 * (TEST_DB_NAME, default `cine_test`) rebuilt once per test run from the
 * migrations and seeds. Each test runs inside a transaction that is rolled
 * back, so tests do not affect each other.
 *
 * Connection: DB_HOST / DB_PORT / DB_USERNAME / DB_PASSWORD
 * (defaults match `docker compose up -d db`: 127.0.0.1:8141).
 * Tests are skipped when the server is not reachable.
 */
abstract class DatabaseTestCase extends TestCase
{
    private static ?PDO $shared = null;
    private static ?string $unavailable = null;
    protected PDO $pdo;

    public static function config(): Config
    {
        $env = static fn (string $k, string $d): string => (string) (getenv($k) ?: $d);

        return Config::fromArray([
            'APP_ENV' => 'testing',
            'APP_TIMEZONE' => 'America/Bogota',
            'DB_HOST' => $env('DB_HOST', '127.0.0.1'),
            'DB_PORT' => $env('DB_PORT', '8141'),
            'DB_DATABASE' => $env('TEST_DB_NAME', 'cine_test'),
            'DB_USERNAME' => $env('DB_USERNAME', 'cine'),
            'DB_PASSWORD' => $env('DB_PASSWORD', 'cine-local-only'),
        ]);
    }

    protected function setUp(): void
    {
        if (self::$unavailable !== null) {
            $this->markTestSkipped(self::$unavailable);
        }
        if (self::$shared === null) {
            try {
                self::$shared = self::rebuild();
            } catch (PDOException $e) {
                self::$unavailable = 'PostgreSQL not available: ' . $e->getMessage();
                $this->markTestSkipped(self::$unavailable);
            }
        }
        $this->pdo = self::$shared;
        $this->pdo->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (isset($this->pdo) && $this->pdo->inTransaction()) {
            $this->pdo->rollBack();
        }
    }

    private static function rebuild(): PDO
    {
        $config = self::config();
        $name = $config->dbName;
        if (preg_match('/^[a-z_]+_test$/', $name) !== 1) {
            throw new \RuntimeException('Refusing to rebuild a database whose name does not end in _test.');
        }

        $admin = Database::connect($config->withDatabase('postgres'));
        $admin->exec("DROP DATABASE IF EXISTS {$name} WITH (FORCE)");
        $admin->exec("CREATE DATABASE {$name}");

        $pdo = Database::connect($config);
        $root = dirname(__DIR__, 2);
        (new Migrator($pdo, $root . '/database/migrations'))->run();
        (new Migrator($pdo, $root . '/database/seeds', 'schema_seed'))->run();

        return $pdo;
    }

    protected function scalar(string $sql, array $params = []): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchColumn();
    }

    /** A future showtime of the given hall that has no tickets yet. */
    protected function freeFutureShowtime(): array
    {
        return $this->pdo->query(
            "SELECT st.showtime_id, st.hall_id FROM showtime st
             WHERE st.starts_at > now() + interval '2 hours'
               AND NOT EXISTS (SELECT 1 FROM ticket t WHERE t.showtime_id = st.showtime_id)
             ORDER BY st.starts_at LIMIT 1",
        )->fetch();
    }

    /** @return list<int> */
    protected function seatsOf(int $hallId, int $count): array
    {
        $stmt = $this->pdo->prepare('SELECT seat_id FROM seat WHERE hall_id = :h ORDER BY seat_id LIMIT :n');
        $stmt->bindValue('h', $hallId, PDO::PARAM_INT);
        $stmt->bindValue('n', $count, PDO::PARAM_INT);
        $stmt->execute();

        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    protected function userId(string $email): int
    {
        return (int) $this->scalar('SELECT user_id FROM app_user WHERE email = :e', ['e' => $email]);
    }
}
