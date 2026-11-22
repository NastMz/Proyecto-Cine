<?php

declare(strict_types=1);

namespace Cine\Admin;

/**
 * Declarative description of a table managed from the admin panel.
 * Every SQL identifier used by the CRUD layer comes from here, never from
 * the request, so user input can only travel as bound parameters.
 */
final class Resource
{
    /**
     * @param list<string> $keys primary-key columns
     * @param array<string, array{0: string, 1?: string}> $columns list columns: name => [label, format]
     * @param list<string> $searchable list columns included in the text search
     * @param list<Field> $fields form fields (empty for read-only resources)
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $title,
        public readonly string $singular,
        public readonly string $group,
        public readonly string $permission,
        public readonly string $table,
        public readonly array $keys,
        public readonly string $listSource,
        public readonly array $columns,
        public readonly array $searchable,
        public readonly string $defaultSort,
        public readonly array $fields = [],
        public readonly bool $editable = true,
        public readonly string $description = '',
        public readonly string $defaultDir = 'asc',
    ) {
    }

    public function isReadOnly(): bool
    {
        return $this->fields === [];
    }

    public function canEdit(): bool
    {
        return !$this->isReadOnly() && $this->editable;
    }

    public function field(string $name): ?Field
    {
        foreach ($this->fields as $field) {
            if ($field->name === $name) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Builds the URL key ("12" or "3~5") of a row.
     *
     * @param array<string, mixed> $row
     */
    public function keyOf(array $row): string
    {
        return implode('~', array_map(static fn (string $k): string => (string) $row[$k], $this->keys));
    }

    /**
     * Parses a URL key into column => value, or null when malformed.
     *
     * @return array<string, int>|null
     */
    public function parseKey(string $key): ?array
    {
        $parts = explode('~', $key);
        if (count($parts) !== count($this->keys)) {
            return null;
        }
        $values = [];
        foreach ($this->keys as $i => $column) {
            if (!ctype_digit($parts[$i])) {
                return null;
            }
            $values[$column] = (int) $parts[$i];
        }

        return $values;
    }
}
