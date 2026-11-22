<?php

declare(strict_types=1);

namespace Cine\Tests\Unit\Http;

use Cine\Controller\AuthController;
use Cine\Http\Csrf;
use Cine\Http\Session;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    public function testTokenIsStableWithinASessionAndValidates(): void
    {
        $csrf = new Csrf(Session::inMemory());
        $token = $csrf->token();

        self::assertSame(64, strlen($token));
        self::assertSame($token, $csrf->token());
        self::assertTrue($csrf->isValid($token));
    }

    public function testRejectsMissingOrForeignTokens(): void
    {
        $csrf = new Csrf(Session::inMemory());
        $csrf->token();
        $other = (new Csrf(Session::inMemory()))->token();

        self::assertFalse($csrf->isValid(''));
        self::assertFalse($csrf->isValid($other));
        self::assertFalse((new Csrf(Session::inMemory()))->isValid('anything'));
    }

    public function testRotateIssuesANewToken(): void
    {
        $csrf = new Csrf(Session::inMemory());
        $old = $csrf->token();
        $csrf->rotate();

        self::assertNotSame($old, $csrf->token());
        self::assertFalse($csrf->isValid($old));
    }

    public function testFlashMessagesAreReadOnce(): void
    {
        $session = Session::inMemory();
        $session->flash('success', 'Listo');

        self::assertSame(['type' => 'success', 'message' => 'Listo'], $session->pull('_flash'));
        self::assertNull($session->pull('_flash'));
    }

    public function testPostLoginRedirectOnlyAllowsLocalPaths(): void
    {
        self::assertSame('/mis-boletos', AuthController::safeNext('/mis-boletos'));
        self::assertSame('/funciones/12?x=1', AuthController::safeNext('/funciones/12?x=1'));
        self::assertSame('/', AuthController::safeNext(''));
    }

    /**
     * Regression: "/<TAB>/evil.example" was accepted and browsers strip the
     * control character, producing a protocol-relative "//evil.example".
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('openRedirects')]
    public function testPostLoginRedirectRejectsOpenRedirects(string $next): void
    {
        self::assertSame('/', AuthController::safeNext($next));
    }

    /**
     * @return array<string, array{string}>
     */
    public static function openRedirects(): array
    {
        return [
            'tab' => [rawurldecode('%2F%09%2Fevil.example')],
            'line feed' => [rawurldecode('%2F%0A%2Fevil.example')],
            'carriage return' => [rawurldecode('%2F%0D%2Fevil.example')],
            'protocol relative' => ['//evil.example'],
            'backslash' => ['/\\evil.example'],
            'absolute https' => ['https://evil.example'],
            'absolute javascript' => ['javascript:alert(1)'],
            'space' => ['/ /evil.example'],
            'no leading slash' => ['evil.example'],
        ];
    }
}
