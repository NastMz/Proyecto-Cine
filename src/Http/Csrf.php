<?php

declare(strict_types=1);

namespace Cine\Http;

/**
 * Synchronizer-token CSRF protection: one random token per session that
 * every state-changing form must send back in the `_token` field.
 */
final class Csrf
{
    public const FIELD = '_token';
    private const KEY = '_csrf_token';

    public function __construct(private readonly Session $session)
    {
    }

    public function token(): string
    {
        $token = $this->session->get(self::KEY);
        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            $this->session->set(self::KEY, $token);
        }

        return $token;
    }

    public function isValid(string $submitted): bool
    {
        $token = $this->session->get(self::KEY);

        return is_string($token) && $submitted !== '' && hash_equals($token, $submitted);
    }

    public function rotate(): void
    {
        $this->session->forget(self::KEY);
    }
}
