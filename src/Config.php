<?php

declare(strict_types=1);

namespace Cine;

/**
 * Application settings read from environment variables (12-factor style).
 * A local `.env` file is loaded when present; real environment variables win.
 */
final class Config
{
    public function __construct(
        public readonly string $env,
        public readonly bool $debug,
        public readonly string $timezone,
        public readonly bool $demo,
        public readonly string $dbHost,
        public readonly int $dbPort,
        public readonly string $dbName,
        public readonly string $dbUser,
        public readonly string $dbPassword,
    ) {
    }

    /**
     * @param array<string, string> $env
     */
    public static function fromArray(array $env): self
    {
        $get = static fn (string $key, string $default = ''): string => (string) ($env[$key] ?? $default);

        return new self(
            env: $get('APP_ENV', 'production'),
            debug: filter_var($get('APP_DEBUG', '0'), FILTER_VALIDATE_BOOL),
            timezone: $get('APP_TIMEZONE', 'America/Bogota'),
            demo: filter_var($get('APP_DEMO', '0'), FILTER_VALIDATE_BOOL),
            dbHost: $get('DB_HOST', '127.0.0.1'),
            dbPort: (int) $get('DB_PORT', '5432'),
            dbName: $get('DB_DATABASE', 'cine'),
            dbUser: $get('DB_USERNAME', 'cine'),
            dbPassword: $get('DB_PASSWORD'),
        );
    }

    public static function load(string $rootDir): self
    {
        $values = self::parseDotEnv($rootDir . '/.env');
        foreach (getenv() as $key => $value) {
            $values[$key] = (string) $value;
        }

        return self::fromArray($values);
    }

    public function withDatabase(string $name): self
    {
        return new self(
            $this->env,
            $this->debug,
            $this->timezone,
            $this->demo,
            $this->dbHost,
            $this->dbPort,
            $name,
            $this->dbUser,
            $this->dbPassword,
        );
    }

    /**
     * Parses simple KEY=value lines; ignores comments and blank lines.
     *
     * @return array<string, string>
     */
    public static function parseDotEnv(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $values = [];
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }
            [$key, $value] = explode('=', $line, 2);
            $values[trim($key)] = trim(trim($value), "\"'");
        }

        return $values;
    }
}
