<?php

declare(strict_types=1);

namespace Cine\Admin;

/**
 * Describes one editable column of an admin resource: how it is rendered
 * in the form and how its submitted value is validated.
 */
final class Field
{
    public const TEXT = 'text';
    public const EMAIL = 'email';
    public const TEXTAREA = 'textarea';
    public const INT = 'int';
    public const DECIMAL = 'decimal';
    public const DATE = 'date';
    public const DATETIME = 'datetime';
    public const SELECT = 'select';
    public const BOOL = 'bool';
    public const PASSWORD = 'password';

    /**
     * @param array<string|int, string>|null $choices fixed options (value => label)
     * @param string|null $optionsSql query returning `value, label` rows
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type = self::TEXT,
        public readonly bool $required = true,
        public readonly ?int $maxLength = null,
        public readonly ?float $min = null,
        public readonly ?float $max = null,
        public readonly ?array $choices = null,
        public readonly ?string $optionsSql = null,
        public readonly ?string $pattern = null,
        public readonly ?string $help = null,
        public readonly bool $editable = true,
    ) {
    }

    public static function text(string $name, string $label, int $maxLength, bool $required = true, ?string $pattern = null, ?string $help = null): self
    {
        return new self($name, $label, self::TEXT, $required, $maxLength, pattern: $pattern, help: $help);
    }

    public static function textarea(string $name, string $label, ?int $maxLength = null, bool $required = true): self
    {
        return new self($name, $label, self::TEXTAREA, $required, $maxLength);
    }

    public static function email(string $name, string $label): self
    {
        return new self($name, $label, self::EMAIL, true, 120);
    }

    public static function int(string $name, string $label, float $min, float $max, bool $required = true, ?string $help = null): self
    {
        return new self($name, $label, self::INT, $required, min: $min, max: $max, help: $help);
    }

    public static function decimal(string $name, string $label, float $min, float $max, bool $required = true, ?string $help = null): self
    {
        return new self($name, $label, self::DECIMAL, $required, min: $min, max: $max, help: $help);
    }

    public static function date(string $name, string $label, bool $required = true): self
    {
        return new self($name, $label, self::DATE, $required);
    }

    public static function dateTime(string $name, string $label): self
    {
        return new self($name, $label, self::DATETIME, true);
    }

    /**
     * @param array<string|int, string> $choices
     */
    public static function choice(string $name, string $label, array $choices, bool $required = true): self
    {
        return new self($name, $label, self::SELECT, $required, choices: $choices);
    }

    public static function relation(string $name, string $label, string $optionsSql, bool $required = true, bool $editable = true): self
    {
        return new self($name, $label, self::SELECT, $required, optionsSql: $optionsSql, editable: $editable);
    }

    public static function bool(string $name, string $label): self
    {
        return new self($name, $label, self::BOOL, false);
    }

    public static function password(string $name, string $label): self
    {
        return new self($name, $label, self::PASSWORD, true, 72, help: 'Mínimo 8 caracteres. Déjalo vacío al editar para conservar la actual.');
    }
}
