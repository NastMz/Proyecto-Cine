<?php

declare(strict_types=1);

/**
 * Escapes a value for safe output inside HTML text and attributes.
 */
function e(mixed $value): string
{
    if ($value === null || is_array($value) || is_object($value)) {
        return '';
    }
    if (is_bool($value)) {
        $value = $value ? '1' : '0';
    }

    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}
