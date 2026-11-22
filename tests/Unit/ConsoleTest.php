<?php

declare(strict_types=1);

namespace Cine\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Regression: demo seeding (accounts with public passwords) must be refused
 * in production. The check runs before any database connection.
 */
final class ConsoleTest extends TestCase
{
    #[DataProvider('demoCommands')]
    public function testDemoCommandsAreRefusedInProduction(string $command): void
    {
        [$code, $stderr] = $this->console($command, ['APP_ENV' => 'production', 'APP_DEMO' => '1', 'DB_HOST' => '192.0.2.1']);

        self::assertSame(1, $code);
        self::assertStringContainsString('refused because APP_ENV=production', $stderr);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function demoCommands(): array
    {
        return ['seed' => ['seed'], 'demo:schedule' => ['demo:schedule']];
    }

    public function testHelpStillWorks(): void
    {
        [$code] = $this->console('help', ['APP_ENV' => 'production']);

        self::assertSame(0, $code);
    }

    /**
     * @param array<string, string> $env
     * @return array{0: int, 1: string}
     */
    private function console(string $command, array $env): array
    {
        $process = proc_open(
            [PHP_BINARY, dirname(__DIR__, 2) . '/bin/console', $command],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes,
            sys_get_temp_dir(),
            $env + array_filter(getenv(), static fn ($k) => !str_starts_with((string) $k, 'APP_') && !str_starts_with((string) $k, 'DB_'), ARRAY_FILTER_USE_KEY),
        );
        stream_get_contents($pipes[1]);
        $stderr = (string) stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return [proc_close($process), $stderr];
    }
}
