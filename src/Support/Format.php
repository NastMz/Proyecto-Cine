<?php

declare(strict_types=1);

namespace Cine\Support;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

/**
 * Spanish (Colombia) presentation helpers that do not depend on ext-intl.
 */
final class Format
{
    private const DAYS = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    private const MONTHS = [
        1 => 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
        'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre',
    ];

    /** 14000 → "$ 14.000", joined by a non-breaking space */
    public static function money(float|int|string|null $amount): string
    {
        return "$\u{00A0}" . number_format((float) $amount, 0, ',', '.');
    }

    public static function date(DateTimeInterface|string $value, string $timezone = 'America/Bogota'): DateTimeImmutable
    {
        $date = $value instanceof DateTimeInterface
            ? DateTimeImmutable::createFromInterface($value)
            : new DateTimeImmutable($value);

        return $date->setTimezone(new DateTimeZone($timezone));
    }

    /** "viernes 27 de noviembre" */
    public static function longDay(DateTimeInterface|string $value): string
    {
        $d = self::date($value);

        return self::DAYS[(int) $d->format('w')] . ' ' . $d->format('j') . ' de ' . self::MONTHS[(int) $d->format('n')];
    }

    /** "vie 27 nov" */
    public static function shortDay(DateTimeInterface|string $value): string
    {
        $d = self::date($value);

        return mb_substr(self::DAYS[(int) $d->format('w')], 0, 3) . ' ' . $d->format('j') . ' '
            . mb_substr(self::MONTHS[(int) $d->format('n')], 0, 3);
    }

    /** "7:00 p. m." */
    public static function time(DateTimeInterface|string $value): string
    {
        $d = self::date($value);

        return $d->format('g:i') . ($d->format('A') === 'AM' ? "\u{00A0}a.\u{00A0}m." : "\u{00A0}p.\u{00A0}m.");
    }

    public static function dateTime(DateTimeInterface|string $value): string
    {
        return self::longDay($value) . ', ' . self::time($value);
    }

    /** 118 → "1 h 58 min" */
    public static function duration(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;

        return ($hours > 0 ? $hours . ' h ' : '') . $rest . ' min';
    }
}
