<?php

declare(strict_types=1);

namespace Cine\Database;

use PDOException;

/**
 * Turns PostgreSQL errors (SQLSTATE) into messages a user can act on.
 * Constraint names are mapped individually where a precise hint helps.
 */
final class DbErrorTranslator
{
    private const CONSTRAINT_MESSAGES = [
        'app_user_email_lower_uq' => 'Ya existe una cuenta con ese correo.',
        'app_user_document_uq' => 'Ya existe una cuenta con ese documento.',
        'showtime_no_overlap_ex' => 'La sala ya tiene una función que se cruza con ese horario.',
        'ticket_seat_showtime_uq' => 'Alguna de las sillas ya fue vendida para esta función.',
        'movie_title_release_uq' => 'Ya existe una película con ese título y fecha de estreno.',
    ];

    private const STATE_MESSAGES = [
        '23505' => 'Ya existe un registro con esos datos.',
        '23503' => 'La operación no es posible: el registro está relacionado con otros datos.',
        '23514' => 'Algún valor no cumple las reglas de la base de datos.',
        '23502' => 'Falta un dato obligatorio.',
        '23P01' => 'El registro se cruza con otro existente.',
        '22001' => 'Un texto supera la longitud permitida.',
        '22003' => 'Un número está fuera del rango permitido.',
        '22P02' => 'Un valor no tiene el formato esperado.',
        '22007' => 'La fecha u hora no es válida.',
        '22008' => 'La fecha u hora está fuera de rango.',
        'CN001' => 'Alguna de las sillas elegidas ya fue vendida. Elige otras.',
        'CN002' => 'La función ya comenzó y no admite más ventas.',
        'CN003' => 'La selección de sillas no es válida.',
        'CN004' => 'El usuario o el medio de pago no están activos.',
        'CN005' => 'La sala ya tiene boletos vendidos: no se puede cambiar su distribución.',
    ];

    public static function message(PDOException $e): string
    {
        $state = self::sqlState($e);
        $text = $e->getMessage();

        foreach (self::CONSTRAINT_MESSAGES as $constraint => $message) {
            if (str_contains($text, '"' . $constraint . '"')) {
                return $message;
            }
        }

        return self::STATE_MESSAGES[$state] ?? 'No fue posible completar la operación en la base de datos.';
    }

    public static function isUserError(PDOException $e): bool
    {
        $state = self::sqlState($e);

        return isset(self::STATE_MESSAGES[$state]);
    }

    public static function sqlState(PDOException $e): string
    {
        $info = $e->errorInfo;

        return is_array($info) && isset($info[0]) ? (string) $info[0] : (string) $e->getCode();
    }
}
