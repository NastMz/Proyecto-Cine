<?php

declare(strict_types=1);

namespace Cine;

use Cine\Admin\CrudRepository;
use Cine\Admin\ResourceRegistry;
use Cine\Auth\Auth;
use Cine\Controller\AdminController;
use Cine\Controller\AuthController;
use Cine\Controller\BookingController;
use Cine\Controller\SiteController;
use Cine\Database\Database;
use Cine\Http\Csrf;
use Cine\Http\Request;
use Cine\Http\Response;
use Cine\Http\Router;
use Cine\Http\Session;
use Cine\Repository\LoginAttemptRepository;
use Cine\Repository\MovieRepository;
use Cine\Repository\PurchaseRepository;
use Cine\Repository\ShowtimeRepository;
use Cine\Repository\StatsRepository;
use Cine\Repository\UserRepository;
use Cine\View\View;
use PDO;
use Throwable;

/**
 * Application kernel: wires services, declares routes and turns a Request
 * into a Response (no globals involved, so it is testable in-process).
 */
final class App
{
    public readonly View $view;
    public readonly Csrf $csrf;
    public readonly ResourceRegistry $resources;
    private ?PDO $pdo;
    private ?Auth $auth = null;
    private Router $router;
    private string $currentPath = '/';

    public function __construct(
        public readonly Config $config,
        public readonly Session $session,
        string $rootDir,
        ?PDO $pdo = null,
    ) {
        $this->pdo = $pdo;
        $this->view = new View($rootDir . '/templates');
        $this->csrf = new Csrf($session);
        $this->resources = new ResourceRegistry();
        $this->router = $this->routes();
    }

    public function pdo(): PDO
    {
        return $this->pdo ??= Database::connect($this->config);
    }

    public function auth(): Auth
    {
        return $this->auth ??= new Auth($this->session, $this->users(), new LoginAttemptRepository($this->pdo()));
    }

    public function users(): UserRepository
    {
        return new UserRepository($this->pdo());
    }

    public function movies(): MovieRepository
    {
        return new MovieRepository($this->pdo());
    }

    public function showtimes(): ShowtimeRepository
    {
        return new ShowtimeRepository($this->pdo());
    }

    public function purchases(): PurchaseRepository
    {
        return new PurchaseRepository($this->pdo());
    }

    public function stats(): StatsRepository
    {
        return new StatsRepository($this->pdo());
    }

    public function crud(): CrudRepository
    {
        return new CrudRepository($this->pdo());
    }

    public function handle(Request $request): Response
    {
        try {
            $response = $this->dispatch($request);
        } catch (Throwable $e) {
            error_log('[cine] ' . $e::class . ': ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
            $response = $this->error(500, 'Algo salió mal', $this->config->debug ? $e->getMessage() : 'Ocurrió un error inesperado. Inténtalo de nuevo en unos minutos.');
        }

        return $this->withSecurityHeaders($response);
    }

    /**
     * Renders a full page with the shared layout variables.
     *
     * @param array<string, mixed> $data
     */
    public function page(string $template, array $data = [], int $status = 200, string $layout = 'layouts/site'): Response
    {
        $this->shareGlobals();

        return Response::html($this->view->render($template, $data, $layout), $status);
    }

    public function error(int $status, string $title, string $message): Response
    {
        try {
            return $this->page('errors/error', ['title' => $title, 'status' => $status, 'message' => $message], $status);
        } catch (Throwable) {
            return Response::html('<h1>' . e($title) . '</h1><p>' . e($message) . '</p>', $status);
        }
    }

    public function redirect(string $to, ?string $flashType = null, ?string $flash = null): Response
    {
        if ($flashType !== null && $flash !== null) {
            $this->session->flash($flashType, $flash);
        }

        return Response::redirect($to);
    }

    private function dispatch(Request $request): Response
    {
        $this->currentPath = $request->path;
        $match = $this->router->match($request->method, $request->path);

        if ($match['handler'] === null) {
            return $match['status'] === 405
                ? $this->error(405, 'Método no permitido', 'Esta dirección no acepta ese tipo de solicitud.')
                : $this->error(404, 'Función no encontrada', 'La página que buscas no está en cartelera.');
        }

        if ($request->method === 'POST' && !$this->csrf->isValid($request->input(Csrf::FIELD))) {
            // 403 rather than 419: Apache does not know 419 and would answer 500.
            return $this->error(403, 'La sesión expiró', 'Recarga la página e inténtalo de nuevo.');
        }

        return ($match['handler'])($request, $match['params']);
    }

    private function shareGlobals(): void
    {
        $auth = $this->auth();
        $this->view->share('authUser', $auth->user());
        $this->view->share('auth', $auth);
        $this->view->share('csrfToken', $this->csrf->token());
        $this->view->share('flash', $this->session->pull('_flash'));
        $this->view->share('currentPath', $this->currentPath);
    }

    private function withSecurityHeaders(Response $response): Response
    {
        return $response
            ->withHeader('X-Content-Type-Options', 'nosniff')
            ->withHeader('X-Frame-Options', 'DENY')
            ->withHeader('Referrer-Policy', 'same-origin')
            ->withHeader(
                'Content-Security-Policy',
                "default-src 'self'; img-src 'self' data:; style-src 'self' https://fonts.googleapis.com; style-src-attr 'unsafe-inline'; "
                . "font-src https://fonts.gstatic.com; script-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'self'",
            );
    }

    private function routes(): Router
    {
        $router = new Router();
        $site = new SiteController($this);
        $booking = new BookingController($this);
        $authController = new AuthController($this);
        $admin = new AdminController($this);

        $router->get('/', $site->home(...));
        $router->get('/peliculas/{id}', $site->movie(...));
        $router->get('/afiches/{id}.svg', $site->poster(...));

        $router->get('/funciones/{id}', $booking->seats(...));
        $router->post('/funciones/{id}/comprar', $booking->purchase(...));
        $router->get('/compras/{id}', $booking->receipt(...));
        $router->get('/mis-boletos', $booking->myTickets(...));

        $router->get('/ingresar', $authController->loginForm(...));
        $router->post('/ingresar', $authController->login(...));
        $router->get('/registro', $authController->registerForm(...));
        $router->post('/registro', $authController->register(...));
        $router->post('/salir', $authController->logout(...));

        $router->get('/admin', $admin->dashboard(...));
        $router->get('/admin/{resource}', $admin->index(...));
        $router->get('/admin/{resource}/nuevo', $admin->create(...));
        $router->post('/admin/{resource}', $admin->store(...));
        $router->get('/admin/{resource}/{key}/editar', $admin->edit(...));
        $router->post('/admin/{resource}/{key}', $admin->update(...));
        $router->post('/admin/{resource}/{key}/eliminar', $admin->destroy(...));

        return $router;
    }
}
