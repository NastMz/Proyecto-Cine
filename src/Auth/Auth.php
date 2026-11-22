<?php

declare(strict_types=1);

namespace Cine\Auth;

use Cine\Http\Session;
use Cine\Repository\LoginAttemptRepository;
use Cine\Repository\UserRepository;

/**
 * Session-based authentication with bcrypt password hashes and a
 * database-backed throttle against password guessing (per account and
 * per client IP), so it survives dropping the session cookie.
 */
final class Auth
{
    private const SESSION_KEY = '_user_id';
    public const MAX_ACCOUNT_FAILURES = 5;
    public const MAX_IP_FAILURES = 20;
    public const WINDOW_MINUTES = 15;
    public const INVALID_MESSAGE = 'Correo o contraseña incorrectos.';
    public const LOCKED_MESSAGE = 'Demasiados intentos fallidos. Espera 15 minutos e inténtalo de nuevo.';
    private const DUMMY_HASH = '$2y$12$0oVPLYIrxclijvsobrK89OBfT65UhFLTPik2rB.68Z9ZwmqTk3gYa';

    /** @var array<string, mixed>|null|false false = not loaded yet */
    private array|null|false $user = false;
    /** @var list<string>|null */
    private ?array $permissions = null;

    public function __construct(
        private readonly Session $session,
        private readonly UserRepository $users,
        private readonly LoginAttemptRepository $attempts,
    ) {
    }

    /**
     * @return array{ok: bool, error: ?string}
     */
    public function attempt(string $email, string $password, string $ip): array
    {
        $failures = $this->attempts->recentFailures($email, $ip, self::WINDOW_MINUTES);
        if ($failures['email'] >= self::MAX_ACCOUNT_FAILURES || $failures['ip'] >= self::MAX_IP_FAILURES) {
            return ['ok' => false, 'error' => self::LOCKED_MESSAGE];
        }

        $row = $this->users->findCredentials($email);
        // Verify against a dummy hash for unknown e-mails so both paths take
        // the same time and do not reveal which accounts exist.
        $hash = $row !== null ? (string) $row['password_hash'] : self::DUMMY_HASH;
        $valid = password_verify($password, $hash) && $row !== null && $row['is_active'];

        if (!$valid) {
            // Same message for unknown, wrong-password and inactive accounts.
            $this->attempts->recordFailure($email, $ip);

            return ['ok' => false, 'error' => self::INVALID_MESSAGE];
        }

        if (password_needs_rehash((string) $row['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->updatePasswordHash((int) $row['user_id'], password_hash($password, PASSWORD_DEFAULT));
        }

        $this->attempts->clear($email);
        $this->login((int) $row['user_id']);

        return ['ok' => true, 'error' => null];
    }

    public function login(int $userId): void
    {
        $this->session->regenerate();
        $this->session->set(self::SESSION_KEY, $userId);
        $this->user = false;
        $this->permissions = null;
    }

    public function logout(): void
    {
        $this->session->clear();
        $this->session->regenerate();
        $this->user = null;
        $this->permissions = [];
    }

    public function id(): ?int
    {
        $id = $this->session->get(self::SESSION_KEY);

        return is_int($id) ? $id : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function user(): ?array
    {
        if ($this->user === false) {
            $id = $this->id();
            $this->user = $id === null ? null : $this->users->findProfile($id);
        }

        return $this->user;
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function can(string $permission): bool
    {
        if (!$this->check()) {
            return false;
        }
        $this->permissions ??= $this->users->permissions((int) $this->user()['user_id']);

        return in_array($permission, $this->permissions, true);
    }
}
