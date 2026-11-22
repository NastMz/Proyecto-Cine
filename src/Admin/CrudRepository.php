<?php

declare(strict_types=1);

namespace Cine\Admin;

use Cine\Database\Database;
use PDO;

/**
 * Generic, parameterized CRUD over a {@see Resource}. Identifiers come only
 * from the resource definition; every value is a bound parameter.
 */
final class CrudRepository
{
    public const PER_PAGE = 15;

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int, pages: int, sort: string, dir: string}
     */
    public function paginate(Resource $resource, string $search = '', string $sort = '', string $dir = '', int $page = 1): array
    {
        $sort = array_key_exists($sort, $resource->columns) ? $sort : $resource->defaultSort;
        $dir = in_array($dir, ['asc', 'desc'], true) ? $dir : $resource->defaultDir;
        $where = '';
        $params = [];

        if ($search !== '' && $resource->searchable !== []) {
            $concat = implode(", ' ', ", array_map(static fn (string $c): string => "COALESCE({$c}::text, '')", $resource->searchable));
            $where = " WHERE concat({$concat}) ILIKE :search";
            $params['search'] = '%' . addcslashes($search, '%_\\') . '%';
        }

        $count = $this->pdo->prepare("SELECT count(*) FROM {$resource->listSource}{$where}");
        $count->execute($params);
        $total = (int) $count->fetchColumn();

        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min(max(1, $page), $pages);

        $keyOrder = implode(', ', $resource->keys);
        $stmt = $this->pdo->prepare(
            "SELECT * FROM {$resource->listSource}{$where}
             ORDER BY {$sort} {$dir} NULLS LAST, {$keyOrder}
             LIMIT :limit OFFSET :offset",
        );
        foreach ($params as $name => $value) {
            $stmt->bindValue($name, $value);
        }
        $stmt->bindValue('limit', self::PER_PAGE, PDO::PARAM_INT);
        $stmt->bindValue('offset', ($page - 1) * self::PER_PAGE, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'pages' => $pages,
            'sort' => $sort,
            'dir' => $dir,
        ];
    }

    /**
     * @param array<string, int> $key
     * @return array<string, mixed>|null
     */
    public function find(Resource $resource, array $key): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM {$resource->table} WHERE {$this->keyClause($resource)}");
        $stmt->execute($key);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @param array<string, mixed> $values validated values, keyed by field name
     */
    public function insert(Resource $resource, array $values): void
    {
        $columns = array_keys($values);
        $placeholders = array_map(static fn (string $c): string => ':' . $c, $columns);
        $stmt = $this->pdo->prepare(sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $resource->table,
            implode(', ', $columns),
            implode(', ', $placeholders),
        ));
        $this->bindAndExecute($stmt, $values);
    }

    /**
     * @param array<string, int> $key
     * @param array<string, mixed> $values
     */
    public function update(Resource $resource, array $key, array $values): void
    {
        if ($values === []) {
            return;
        }
        $assignments = array_map(static fn (string $c): string => "{$c} = :{$c}", array_keys($values));
        $params = $values;
        $where = [];
        foreach ($key as $column => $value) {
            $where[] = "{$column} = :key_{$column}";
            $params['key_' . $column] = $value;
        }

        $stmt = $this->pdo->prepare(sprintf(
            'UPDATE %s SET %s WHERE %s',
            $resource->table,
            implode(', ', $assignments),
            implode(' AND ', $where),
        ));
        $this->bindAndExecute($stmt, $params);
    }

    /**
     * @param array<string, int> $key
     */
    public function delete(Resource $resource, array $key): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM {$resource->table} WHERE {$this->keyClause($resource)}");
        Database::atomic($this->pdo, static fn () => $stmt->execute($key));

        return $stmt->rowCount() > 0;
    }

    /**
     * Options (value => label) for every relation field of the resource.
     *
     * @return array<string, array<string|int, string>>
     */
    public function relationOptions(Resource $resource): array
    {
        $options = [];
        foreach ($resource->fields as $field) {
            if ($field->optionsSql === null) {
                continue;
            }
            $options[$field->name] = $this->pdo->query($field->optionsSql)->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        return $options;
    }

    private function keyClause(Resource $resource): string
    {
        return implode(' AND ', array_map(static fn (string $k): string => "{$k} = :{$k}", $resource->keys));
    }

    /**
     * @param array<string, mixed> $params
     */
    private function bindAndExecute(\PDOStatement $stmt, array $params): void
    {
        Database::atomic($this->pdo, function () use ($stmt, $params): void {
            $this->bind($stmt, $params);
            $stmt->execute();
        });
    }

    /**
     * @param array<string, mixed> $params
     */
    private function bind(\PDOStatement $stmt, array $params): void
    {
        foreach ($params as $name => $value) {
            $type = match (true) {
                is_bool($value) => PDO::PARAM_BOOL,
                is_int($value) => PDO::PARAM_INT,
                $value === null => PDO::PARAM_NULL,
                default => PDO::PARAM_STR,
            };
            $stmt->bindValue($name, is_float($value) ? (string) $value : $value, $type);
        }
    }
}
