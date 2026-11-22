<?php

declare(strict_types=1);

namespace Cine\Auth;

use Cine\Http\Session;
use Cine\Repository\UserRepository;

/**
 * Session-based authentication with bcrypt password hashes and a small
 * per-session throttle against password guessing.
 */
final class Auth
{
    private const SESSION_KEY = '_user_id';
    private const ATTEMPTS_KEY = '_login_attempts';
    public const MAX_ATTEMPTS = 5;
    public const LOCK_SECONDS = 60;
    private const DUMMY_HASH = '$2y$12$0oVPLYIrxclijvsobrK89OBfT65UhFLTPik2rB.68Z9ZwmqTk3gYa';

    /** @var array<string, mixed>|null|false false = not loaded yet */
    private array|null|false $user = false;
    /** @var list<string>|null */
    private ?array $permissions = null;

    public function __construct(
        private readonly Session $session,
        private readonly UserRepository $users,
        private readonly \Closure $clock,
    ) {
    }

    public static function create(Session $session, UserRepository $users): self
    {
        return new self($session, $users, static fn (): int => time());
    }

    /**
     * @return array{ok: bool, error: ?string}
     */
    public function attempt(string $email, string $password): array
    {
        $now = ($this->clock)();
        $attempts = $this->session->get(self::ATTEMPTS_KEY, ['count' => 0, 'until' => 0]);
        if ($attempts['until'] > $now) {
            return ['ok' => false, 'error' => 'Demasiados intentos. Espera un minuto e inténtalo de nuevo.'];
        }

        $row = $this->users->findCredentials($email);
        // Verify against a dummy hash for unknown e-mails so both paths take
        // the same time and do not reveal which accounts exist.
        $hash = $row !== null ? (string) $row['password_hash'] : self::DUMMY_HASH;
        $valid = password_verify($password, $hash) && $row !== null;

        if (!$valid || !$row['is_active']) {
            $count = $attempts['count'] + 1;
            $this->session->set(self::ATTEMPTS_KEY, [
                'count' => $count >= self::MAX_ATTEMPTS ? 0 : $count,
                'until' => $count >= self::MAX_ATTEMPTS ? $now + self::LOCK_SECONDS : 0,
            ]);

            return ['ok' => false, 'error' => $valid ? 'La cuenta está inactiva.' : 'Correo o contraseña incorrectos.'];
        }

        if (password_needs_rehash((string) $row['password_hash'], PASSWORD_DEFAULT)) {
            $this->users->updatePasswordHash((int) $row['user_id'], password_hash($password, PASSWORD_DEFAULT));
        }

        $this->login((int) $row['user_id']);

        return ['ok' => true, 'error' => null];
    }

    public function login(int $userId): void
    {
        $this->session->regenerate();
        $this->session->forget(self::ATTEMPTS_KEY);
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
