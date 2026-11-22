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
        self::assertSame('/', AuthController::safeNext('https://evil.example'));
        self::assertSame('/', AuthController::safeNext('//evil.example'));
        self::assertSame('/', AuthController::safeNext('/\\evil.example'));
        self::assertSame('/', AuthController::safeNext(''));
    }
}
