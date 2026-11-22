<?php

declare(strict_types=1);

namespace Cine\Tests\Unit\Http;

use Cine\Http\Request;
use Cine\Http\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        $this->router->get('/', static fn () => 'home');
        $this->router->get('/peliculas/{id}', static fn () => 'movie');
        $this->router->get('/admin/{resource}/{key}/editar', static fn () => 'edit');
        $this->router->post('/admin/{resource}', static fn () => 'store');
    }

    public function testMatchesStaticAndParameterizedRoutes(): void
    {
        self::assertSame('home', ($this->router->match('GET', '/')['handler'])());

        $match = $this->router->match('GET', '/peliculas/42');
        self::assertSame(200, $match['status']);
        self::assertSame(['id' => '42'], $match['params']);
    }

    public function testIdPlaceholdersOnlyAcceptDigits(): void
    {
        self::assertSame(404, $this->router->match('GET', '/peliculas/abc')['status']);
        self::assertSame(404, $this->router->match('GET', "/peliculas/1' OR 1=1")['status']);
    }

    public function testSlugPlaceholdersAcceptCompositeKeys(): void
    {
        $match = $this->router->match('GET', '/admin/permisos-rol/3~5/editar');

        self::assertSame(['resource' => 'permisos-rol', 'key' => '3~5'], $match['params']);
    }

    public function testWrongMethodReturns405AndHeadFallsBackToGet(): void
    {
        self::assertSame(405, $this->router->match('GET', '/admin/peliculas')['status']);
        self::assertSame(200, $this->router->match('HEAD', '/')['status']);
    }

    public function testRequestPathIsNormalized(): void
    {
        self::assertSame('/peliculas/3', Request::create('GET', '/peliculas/3/?x=1')->path);
        self::assertSame('/', Request::create('GET', '')->path);
        self::assertSame('1', Request::create('GET', '/a?x=1')->query('x'));
    }

    public function testIntListKeepsOnlyPositiveUniqueIntegers(): void
    {
        $request = Request::create('POST', '/', ['seats' => ['3', '3', '-1', 'x', '0', '12', ['nested']]]);

        self::assertSame([3, 12], $request->intList('seats'));
        self::assertSame([], Request::create('POST', '/', ['seats' => 'nope'])->intList('seats'));
    }
}
