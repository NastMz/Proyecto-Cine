<?php

declare(strict_types=1);

namespace Cine\Database;

use PDO;
use RuntimeException;
use Throwable;

/**
 * Applies versioned `.sql` files in file-name order, each one inside its own
 * transaction, and records them in a bookkeeping table so they run once.
 * The same class runs migrations (`schema_migration`) and seeds (`schema_seed`).
 */
final class Migrator
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $directory,
        private readonly string $table = 'schema_migration',
    ) {
        if (preg_match('/^[a-z_]+$/', $table) !== 1) {
            throw new RuntimeException('Invalid bookkeeping table name.');
        }
    }

    /**
     * @return list<string> files applied by this call
     */
    public function run(): array
    {
        $this->ensureTable();
        $applied = $this->applied();
        $done = [];

        foreach ($this->pending($applied) as $file) {
            $sql = (string) file_get_contents($this->directory . '/' . $file);
            $this->pdo->beginTransaction();
            try {
                $this->pdo->exec($sql);
                $insert = $this->pdo->prepare(
                    "INSERT INTO {$this->table} (version, checksum) VALUES (:version, :checksum)",
                );
                $insert->execute(['version' => $file, 'checksum' => hash('sha256', $sql)]);
                $this->pdo->commit();
            } catch (Throwable $e) {
                $this->pdo->rollBack();
                throw new RuntimeException("Failed to apply {$file}: " . $e->getMessage(), 0, $e);
            }
            $done[] = $file;
        }

        return $done;
    }

    /**
     * @param list<string> $applied
     * @return list<string>
     */
    public function pending(array $applied = []): array
    {
        $files = array_map('basename', glob($this->directory . '/*.sql') ?: []);
        sort($files, SORT_STRING);

        return array_values(array_diff($files, $applied));
    }

    /**
     * @return list<string>
     */
    public function applied(): array
    {
        $this->ensureTable();

        return $this->pdo->query("SELECT version FROM {$this->table} ORDER BY version")
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    private function ensureTable(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS {$this->table} (
                version    varchar(150) PRIMARY KEY,
                checksum   char(64)     NOT NULL,
                applied_at timestamptz  NOT NULL DEFAULT now()
            )",
        );
    }
}
