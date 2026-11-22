<?php

declare(strict_types=1);

namespace Cine\Http;

/**
 * Immutable view of the incoming HTTP request.
 */
final class Request
{
    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     * @param array<string, mixed> $server
     */
    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query = [],
        private readonly array $body = [],
        private readonly array $server = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        $uri = (string) ($_SERVER['REQUEST_URI'] ?? '/');
        $path = (string) parse_url($uri, PHP_URL_PATH);

        return new self(
            strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')),
            self::normalizePath($path),
            $_GET,
            $_POST,
            $_SERVER,
        );
    }

    /**
     * @param array<string, mixed> $query
     * @param array<string, mixed> $body
     */
    public static function create(string $method, string $uri, array $body = [], array $query = [], array $server = []): self
    {
        $path = (string) parse_url($uri, PHP_URL_PATH);
        $queryString = (string) parse_url($uri, PHP_URL_QUERY);
        parse_str($queryString, $parsed);

        return new self(strtoupper($method), self::normalizePath($path), $query + $parsed, $body, $server);
    }

    public static function normalizePath(string $path): string
    {
        $path = '/' . trim(rawurldecode($path), '/');

        return $path === '' ? '/' : $path;
    }

    public function query(string $key, string $default = ''): string
    {
        $value = $this->query[$key] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function input(string $key, string $default = ''): string
    {
        $value = $this->body[$key] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    /**
     * Returns a list of positive integers sent as `name[]`.
     *
     * @return list<int>
     */
    public function intList(string $key): array
    {
        $raw = $this->body[$key] ?? [];
        if (!is_array($raw)) {
            return [];
        }

        $ids = [];
        foreach ($raw as $value) {
            if (is_scalar($value) && self::isId((string) $value)) {
                $ids[] = (int) $value;
            }
        }

        return array_values(array_unique($ids));
    }

    /**
     * True for a positive integer that fits a PostgreSQL `integer` column
     * (or a smaller `$max`).
     */
    public static function isId(string $value, int $max = 2147483647): bool
    {
        return preg_match('/^[1-9][0-9]{0,9}$/', $value) === 1 && (int) $value <= $max;
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->body;
    }

    /** Client address as seen by the web server (no proxy headers trusted). */
    public function ip(): string
    {
        $ip = (string) ($this->server['REMOTE_ADDR'] ?? '');

        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : '0.0.0.0';
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? '') === 'on'
            || ($this->server['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
    }
}
