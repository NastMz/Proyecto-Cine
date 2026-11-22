<?php

declare(strict_types=1);

namespace Cine\Database;

use Cine\Config;
use PDO;

final class Database
{
    public static function connect(Config $config): PDO
    {
        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s',
            $config->dbHost,
            $config->dbPort,
            $config->dbName,
        );

        $pdo = new PDO($dsn, $config->dbUser, $config->dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        $pdo->exec("SET TIME ZONE " . $pdo->quote($config->timezone));

        return $pdo;
    }

    /**
     * Runs a write so that, if it fails, an outer transaction stays usable:
     * inside a transaction it is wrapped in a savepoint; otherwise
     * PostgreSQL's per-statement atomicity already applies.
     *
     * @template T
     * @param callable(): T $operation
     * @return T
     */
    public static function atomic(PDO $pdo, callable $operation): mixed
    {
        if (!$pdo->inTransaction()) {
            return $operation();
        }

        $pdo->exec('SAVEPOINT cine_atomic');
        try {
            $result = $operation();
        } catch (\Throwable $e) {
            $pdo->exec('ROLLBACK TO SAVEPOINT cine_atomic');
            throw $e;
        }
        $pdo->exec('RELEASE SAVEPOINT cine_atomic');

        return $result;
    }
}
