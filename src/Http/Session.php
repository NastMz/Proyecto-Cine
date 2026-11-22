<?php

declare(strict_types=1);

namespace Cine\Http;

/**
 * Session storage. The default implementation wraps PHP's native session;
 * tests use an in-memory array through the same API.
 */
class Session
{
    /** @var array<string, mixed> */
    private array $memory = [];

    public function __construct(private readonly bool $native = false)
    {
    }

    public static function native(bool $secureCookie): self
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            ini_set('session.use_strict_mode', '1');
            ini_set('session.use_only_cookies', '1');
            session_name('butaca_session');
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'secure' => $secureCookie,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start();
        }

        return new self(true);
    }

    public static function inMemory(): self
    {
        return new self(false);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->store()[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        if ($this->native) {
            $_SESSION[$key] = $value;
        } else {
            $this->memory[$key] = $value;
        }
    }

    public function forget(string $key): void
    {
        if ($this->native) {
            unset($_SESSION[$key]);
        } else {
            unset($this->memory[$key]);
        }
    }

    /** Reads a value and removes it (one-time "flash" messages). */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    public function flash(string $type, string $message): void
    {
        $this->set('_flash', ['type' => $type, 'message' => $message]);
    }

    /** Prevents session fixation after a privilege change (login/logout). */
    public function regenerate(): void
    {
        if ($this->native && session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function clear(): void
    {
        if ($this->native) {
            $_SESSION = [];
        } else {
            $this->memory = [];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function store(): array
    {
        return $this->native ? ($_SESSION ?? []) : $this->memory;
    }
}
