<?php

declare(strict_types=1);

namespace Cine\Tests\Integration;

use Cine\App;
use Cine\Auth\Auth;
use Cine\Http\Request;
use Cine\Http\Response;
use Cine\Http\Session;

/**
 * Smoke test of the key pages through the real kernel (in-process).
 */
final class SmokeTest extends DatabaseTestCase
{
    private App $app;
    private Session $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->session = Session::inMemory();
        $this->app = new App(self::config(), $this->session, dirname(__DIR__, 2), $this->pdo);
    }

    public function testPublicPagesRender(): void
    {
        $movieId = (int) $this->scalar('SELECT min(movie_id) FROM movie');
        $showtime = $this->freeFutureShowtime();

        $home = $this->get('/');
        self::assertSame(200, $home->status);
        self::assertStringContainsString('Esta semana en Butaca', $home->body);
        self::assertStringContainsString('Content-Security-Policy', implode("\n", array_keys($home->headers)));

        self::assertSame(200, $this->get('/peliculas/' . $movieId)->status);
        self::assertStringContainsString('Elige tus sillas', $this->get('/funciones/' . $showtime['showtime_id'])->body);
        self::assertSame(200, $this->get('/ingresar')->status);
        self::assertSame(200, $this->get('/registro')->status);

        $poster = $this->get('/afiches/' . $movieId . '.svg');
        self::assertSame('image/svg+xml; charset=UTF-8', $poster->headers['Content-Type']);
        self::assertStringStartsWith('<svg', $poster->body);
    }

    public function testUnknownPagesReturn404(): void
    {
        self::assertSame(404, $this->get('/no-existe')->status);
        self::assertSame(404, $this->get('/peliculas/999999')->status);
        self::assertSame(405, $this->app->handle(Request::create('POST', '/peliculas/1', ['_token' => 'x']))->status);
    }

    public function testPostWithoutCsrfTokenIsRejected(): void
    {
        $response = $this->app->handle(Request::create('POST', '/ingresar', [
            'email' => 'admin@butaca.test',
            'password' => 'Admin.2026',
        ]));

        self::assertSame(403, $response->status);
        self::assertNull($this->session->get('_user_id'));
    }

    public function testOutOfRangeIdsReturn404InsteadOfDatabaseErrors(): void
    {
        $this->post('/ingresar', ['email' => 'admin@butaca.test', 'password' => 'Admin.2026']);

        self::assertSame(404, $this->get('/peliculas/99999999999')->status);
        self::assertSame(404, $this->get('/funciones/2147483648')->status);
        self::assertSame(404, $this->get('/compras/99999999999999999999')->status);
        self::assertSame(404, $this->get('/admin/peliculas/99999999999/editar')->status);
        self::assertSame(404, $this->post('/admin/peliculas/99999999999/eliminar', [])->status);
        self::assertSame(404, $this->get('/afiches/99999999999.svg')->status);
    }

    public function testOutOfRangePurchaseValuesAreRejectedGracefully(): void
    {
        $this->post('/ingresar', ['email' => 'andres@butaca.test', 'password' => 'Cliente.2026']);
        $showtime = $this->freeFutureShowtime();
        $seat = (string) $this->seatsOf((int) $showtime['hall_id'], 1)[0];
        $url = '/funciones/' . $showtime['showtime_id'] . '/comprar';

        $method = $this->post($url, ['seats' => [$seat], 'payment_method_id' => '99999']);
        self::assertSame(303, $method->status);
        self::assertSame('Elige un medio de pago.', $this->session->get('_flash')['message']);

        $seats = $this->post($url, ['seats' => ['99999999999'], 'payment_method_id' => '1']);
        self::assertSame(303, $seats->status);
        self::assertSame('Selecciona al menos una silla.', $this->session->get('_flash')['message']);
    }

    public function testProtectedPagesRedirectGuestsToLogin(): void
    {
        foreach (['/admin', '/admin/peliculas', '/mis-boletos'] as $path) {
            $response = $this->get($path);
            self::assertSame(303, $response->status, $path);
            self::assertStringStartsWith('/ingresar', $response->headers['Location']);
        }
    }

    public function testWrongPasswordShowsAnError(): void
    {
        $response = $this->post('/ingresar', ['email' => 'admin@butaca.test', 'password' => 'incorrecta']);

        self::assertSame(422, $response->status);
        self::assertStringContainsString('Correo o contraseña incorrectos', $response->body);
    }

    public function testInactiveAccountsGetTheGenericMessage(): void
    {
        $this->pdo->exec("UPDATE app_user SET is_active = false WHERE email = 'marta@butaca.test'");

        $response = $this->post('/ingresar', ['email' => 'marta@butaca.test', 'password' => 'Cliente.2026']);

        self::assertSame(422, $response->status);
        self::assertStringContainsString(Auth::INVALID_MESSAGE, $response->body);
        self::assertStringNotContainsString('inactiva', $response->body);
        self::assertNull($this->session->get('_user_id'));
    }

    /**
     * Regression: the throttle lived in the session, so a client that drops
     * its cookie got unlimited attempts. Each attempt here uses a brand-new
     * session (no cookie) and the lock still applies.
     */
    public function testAccountIsLockedAfterRepeatedFailuresEvenWithoutCookies(): void
    {
        for ($i = 0; $i < Auth::MAX_ACCOUNT_FAILURES; $i++) {
            $failed = $this->loginFromFreshSession('admin@butaca.test', 'incorrecta', '203.0.113.' . ($i + 1));
            self::assertStringContainsString(Auth::INVALID_MESSAGE, $failed->body);
        }

        $locked = $this->loginFromFreshSession('ADMIN@butaca.test', 'Admin.2026', '198.51.100.9');
        self::assertSame(422, $locked->status);
        self::assertStringContainsString(Auth::LOCKED_MESSAGE, $locked->body);

        // Other accounts from other addresses are unaffected.
        self::assertSame(303, $this->loginFromFreshSession('laura@butaca.test', 'Cliente.2026', '198.51.100.9')->status);
        self::assertSame(1, (int) $this->scalar("SELECT count(DISTINCT email) FROM login_attempt"));
    }

    public function testClientIpIsLockedAfterFailuresAcrossManyAccounts(): void
    {
        $ip = '192.0.2.50';
        for ($i = 0; $i < Auth::MAX_IP_FAILURES; $i++) {
            $this->loginFromFreshSession("nadie{$i}@example.test", 'x', $ip);
        }

        $locked = $this->loginFromFreshSession('laura@butaca.test', 'Cliente.2026', $ip);
        self::assertStringContainsString(Auth::LOCKED_MESSAGE, $locked->body);

        self::assertSame(303, $this->loginFromFreshSession('laura@butaca.test', 'Cliente.2026', '192.0.2.51')->status);
    }

    public function testSuccessfulLoginResetsTheAccountCounter(): void
    {
        for ($i = 0; $i < Auth::MAX_ACCOUNT_FAILURES - 1; $i++) {
            $this->loginFromFreshSession('andres@butaca.test', 'incorrecta', '203.0.113.20');
        }
        self::assertSame(303, $this->loginFromFreshSession('andres@butaca.test', 'Cliente.2026', '203.0.113.20')->status);
        self::assertSame(0, (int) $this->scalar("SELECT count(*) FROM login_attempt WHERE email = 'andres@butaca.test'"));
    }

    public function testAdminCanBrowseEveryModuleAndCreateRecords(): void
    {
        $login = $this->post('/ingresar', ['email' => 'admin@butaca.test', 'password' => 'Admin.2026', 'next' => '/']);
        self::assertSame('/admin', $login->headers['Location']);

        self::assertStringContainsString('Taquilla de los últimos 7 días', $this->get('/admin')->body);
        foreach ($this->app->resources->all() as $resource) {
            self::assertSame(200, $this->get('/admin/' . $resource->slug)->status, $resource->slug);
            if (!$resource->isReadOnly()) {
                self::assertSame(200, $this->get('/admin/' . $resource->slug . '/nuevo')->status, $resource->slug);
            }
        }

        $created = $this->post('/admin/actores', ['full_name' => 'Actriz <b>Smoke</b>', 'nationality' => 'Colombiana']);
        self::assertSame(303, $created->status);
        $list = $this->get('/admin/actores?q=Smoke');
        self::assertStringContainsString('Actriz &lt;b&gt;Smoke&lt;/b&gt;', $list->body);
        self::assertStringNotContainsString('<b>Smoke</b>', $list->body);

        $invalid = $this->post('/admin/actores', ['full_name' => '']);
        self::assertSame(422, $invalid->status);
        self::assertStringContainsString('Este campo es obligatorio', $invalid->body);
    }

    public function testAdminSeesFriendlyErrorWhenShowtimesOverlap(): void
    {
        $this->post('/ingresar', ['email' => 'admin@butaca.test', 'password' => 'Admin.2026']);
        $existing = $this->pdo->query('SELECT movie_id, hall_id, starts_at FROM showtime WHERE starts_at > now() LIMIT 1')->fetch();
        $start = (new \DateTimeImmutable($existing['starts_at']))->setTimezone(new \DateTimeZone('America/Bogota'));

        $response = $this->post('/admin/funciones', [
            'movie_id' => (string) $existing['movie_id'],
            'hall_id' => (string) $existing['hall_id'],
            'starts_at' => $start->modify('+15 minutes')->format('Y-m-d\TH:i'),
            'language' => 'Doblada',
            'price_override' => '',
        ]);

        self::assertSame(422, $response->status);
        self::assertStringContainsString('La sala ya tiene una función que se cruza con ese horario.', $response->body);
    }

    public function testCustomerCannotOpenTheAdminPanel(): void
    {
        $this->post('/ingresar', ['email' => 'laura@butaca.test', 'password' => 'Cliente.2026']);

        self::assertSame(403, $this->get('/admin')->status);
    }

    public function testCashierOnlySeesSalesModules(): void
    {
        $this->post('/ingresar', ['email' => 'taquilla@butaca.test', 'password' => 'Taquilla.2026']);

        self::assertSame(200, $this->get('/admin/compras')->status);
        self::assertSame(403, $this->get('/admin/usuarios')->status);
        self::assertSame(403, $this->post('/admin/peliculas/1/eliminar', [])->status);
    }

    public function testCustomerCanRegisterBuyAndSeeTheTickets(): void
    {
        $register = $this->post('/registro', [
            'first_name' => 'Valen',
            'last_name' => 'Prueba',
            'document' => '55555555',
            'phone' => '',
            'email' => 'valen@example.test',
            'password_hash' => 'cine-1234',
            'password_confirmation' => 'cine-1234',
        ]);
        self::assertSame(303, $register->status);
        self::assertNotNull($this->session->get('_user_id'));

        $showtime = $this->freeFutureShowtime();
        $seats = $this->seatsOf((int) $showtime['hall_id'], 2);
        $method = (string) $this->scalar('SELECT min(payment_method_id) FROM payment_method');

        $buy = $this->post('/funciones/' . $showtime['showtime_id'] . '/comprar', [
            'seats' => array_map('strval', $seats),
            'payment_method_id' => $method,
        ]);
        self::assertSame(303, $buy->status);
        self::assertMatchesRegularExpression('#^/compras/\d+$#', $buy->headers['Location']);

        $receipt = $this->get($buy->headers['Location']);
        self::assertStringContainsString('Tus boletos están listos', $receipt->body);

        $again = $this->post('/funciones/' . $showtime['showtime_id'] . '/comprar', [
            'seats' => [(string) $seats[0]],
            'payment_method_id' => $method,
        ]);
        self::assertSame('/funciones/' . $showtime['showtime_id'], $again->headers['Location']);
        self::assertSame('Alguna de las sillas elegidas ya fue vendida. Elige otras.', $this->session->get('_flash')['message']);

        self::assertStringContainsString('Próximas funciones', $this->get('/mis-boletos')->body);
    }

    public function testRegistrationRejectsDuplicateEmail(): void
    {
        $response = $this->post('/registro', [
            'first_name' => 'Otra',
            'last_name' => 'Persona',
            'document' => '66666666',
            'email' => 'LAURA@butaca.test',
            'password_hash' => 'cine-1234',
            'password_confirmation' => 'cine-1234',
        ]);

        self::assertSame(422, $response->status);
        self::assertStringContainsString('Ya existe una cuenta con ese correo.', $response->body);
    }

    private function loginFromFreshSession(string $email, string $password, string $ip): Response
    {
        $session = Session::inMemory();
        $app = new App(self::config(), $session, dirname(__DIR__, 2), $this->pdo);

        return $app->handle(Request::create(
            'POST',
            '/ingresar',
            ['email' => $email, 'password' => $password, '_token' => $app->csrf->token()],
            server: ['REMOTE_ADDR' => $ip],
        ));
    }

    private function get(string $uri): Response
    {
        return $this->app->handle(Request::create('GET', $uri));
    }

    /**
     * @param array<string, mixed> $body
     */
    private function post(string $uri, array $body): Response
    {
        return $this->app->handle(Request::create('POST', $uri, $body + ['_token' => $this->app->csrf->token()]));
    }
}
