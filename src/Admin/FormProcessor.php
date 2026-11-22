<?php

declare(strict_types=1);

namespace Cine\Admin;

use DateTimeImmutable;

/**
 * Validates and normalizes submitted admin forms against the resource's
 * field definitions. Unknown request keys are ignored, so a client can never
 * write a column that is not declared in the resource.
 */
final class FormProcessor
{
    /**
     * @param array<string, mixed> $input raw request body
     * @param array<string, array<string|int, string>> $options allowed values for relation fields
     * @return array{values: array<string, string|int|float|bool|null>, errors: array<string, string>}
     */
    public function process(Resource $resource, array $input, bool $isUpdate, array $options = []): array
    {
        $values = [];
        $errors = [];

        foreach ($resource->fields as $field) {
            if ($isUpdate && !$field->editable) {
                continue;
            }

            $raw = $input[$field->name] ?? null;
            $raw = is_scalar($raw) ? trim((string) $raw) : '';

            if ($field->type === Field::BOOL) {
                $values[$field->name] = in_array($raw, ['1', 'on', 'true'], true);
                continue;
            }

            if ($field->type === Field::PASSWORD) {
                if ($raw === '' && $isUpdate) {
                    continue; // keep the current password
                }
                if (mb_strlen($raw) < 8 || strlen($raw) > 72) {
                    $errors[$field->name] = 'La contraseña debe tener entre 8 y 72 caracteres.';
                    continue;
                }
                $values[$field->name] = password_hash($raw, PASSWORD_DEFAULT);
                continue;
            }

            if ($raw === '') {
                if ($field->required) {
                    $errors[$field->name] = 'Este campo es obligatorio.';
                } else {
                    $values[$field->name] = null;
                }
                continue;
            }

            [$value, $error] = $this->convert($field, $raw, $options[$field->name] ?? null);
            if ($error !== null) {
                $errors[$field->name] = $error;
            } else {
                $values[$field->name] = $value;
            }
        }

        return ['values' => $values, 'errors' => $errors];
    }

    /**
     * @param array<string|int, string>|null $relationOptions
     * @return array{0: string|int|float|null, 1: ?string}
     */
    private function convert(Field $field, string $raw, ?array $relationOptions): array
    {
        switch ($field->type) {
            case Field::INT:
                if (preg_match('/^-?\d+$/', $raw) !== 1) {
                    return [null, 'Debe ser un número entero.'];
                }

                return $this->range($field, (int) $raw);

            case Field::DECIMAL:
                $normalized = str_replace(',', '.', $raw);
                if (!is_numeric($normalized)) {
                    return [null, 'Debe ser un número.'];
                }

                return $this->range($field, (float) $normalized);

            case Field::DATE:
                $date = DateTimeImmutable::createFromFormat('!Y-m-d', $raw);
                if ($date === false || $date->format('Y-m-d') !== $raw) {
                    return [null, 'Fecha no válida (AAAA-MM-DD).'];
                }

                return [$raw, null];

            case Field::DATETIME:
                $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $raw)
                    ?: DateTimeImmutable::createFromFormat('!Y-m-d H:i', $raw);
                if ($date === false) {
                    return [null, 'Fecha y hora no válidas.'];
                }

                return [$date->format('Y-m-d H:i:00'), null];

            case Field::EMAIL:
                if (filter_var($raw, FILTER_VALIDATE_EMAIL) === false || mb_strlen($raw) > 120) {
                    return [null, 'Correo electrónico no válido.'];
                }

                return [mb_strtolower($raw), null];

            case Field::SELECT:
                $allowed = $field->choices ?? $relationOptions ?? [];
                if (!array_key_exists($raw, $allowed)) {
                    return [null, 'Selecciona una opción válida.'];
                }

                return [ctype_digit($raw) && $field->optionsSql !== null ? (int) $raw : $raw, null];

            default: // text, textarea
                if ($field->maxLength !== null && mb_strlen($raw) > $field->maxLength) {
                    return [null, "Máximo {$field->maxLength} caracteres."];
                }
                if ($field->pattern !== null && preg_match('/' . str_replace('/', '\/', $field->pattern) . '/u', $raw) !== 1) {
                    return [null, $field->help ?? 'Formato no válido.'];
                }

                return [$raw, null];
        }
    }

    /**
     * @return array{0: int|float|null, 1: ?string}
     */
    private function range(Field $field, int|float $value): array
    {
        if ($field->min !== null && $value < $field->min) {
            return [null, 'Debe ser mayor o igual a ' . $field->min . '.'];
        }
        if ($field->max !== null && $value > $field->max) {
            return [null, 'Debe ser menor o igual a ' . $field->max . '.'];
        }

        return [$value, null];
    }
}
