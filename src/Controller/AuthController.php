<?php

declare(strict_types=1);

namespace Cine\Controller;

use Cine\Admin\Field;
use Cine\Admin\FormProcessor;
use Cine\Admin\Resource;
use Cine\App;
use Cine\Database\DbErrorTranslator;
use Cine\Http\Request;
use Cine\Http\Response;
use PDOException;

final class AuthController
{
    public function __construct(private readonly App $app)
    {
    }

    /** @param array<string, string> $params */
    public function loginForm(Request $request, array $params): Response
    {
        if ($this->app->auth()->check()) {
            return $this->app->redirect('/');
        }

        return $this->app->page('auth/login', [
            'pageTitle' => 'Ingresar',
            'next' => self::safeNext($request->query('next')),
            'email' => '',
            'error' => null,
        ]);
    }

    /** @param array<string, string> $params */
    public function login(Request $request, array $params): Response
    {
        $email = $request->input('email');
        $next = self::safeNext($request->input('next'));
        $result = $this->app->auth()->attempt($email, $request->input('password'), $request->ip());

        if (!$result['ok']) {
            return $this->app->page('auth/login', [
                'pageTitle' => 'Ingresar',
                'next' => $next,
                'email' => $email,
                'error' => $result['error'],
            ], 422);
        }

        $this->app->csrf->rotate();
        if ($next === '/' && $this->app->auth()->can('admin.access')) {
            $next = '/admin';
        }

        return $this->app->redirect($next, 'success', 'Hola de nuevo, ' . $this->app->auth()->user()['first_name'] . '.');
    }

    /** @param array<string, string> $params */
    public function registerForm(Request $request, array $params): Response
    {
        return $this->app->page('auth/register', [
            'pageTitle' => 'Crear cuenta',
            'old' => [],
            'errors' => [],
        ]);
    }

    /** @param array<string, string> $params */
    public function register(Request $request, array $params): Response
    {
        $users = $this->app->users();
        $result = (new FormProcessor())->process(self::registrationForm(), $request->all(), false);
        $errors = $result['errors'];
        if ($request->input('password_hash') !== $request->input('password_confirmation')) {
            $errors['password_confirmation'] = 'Las contraseñas no coinciden.';
        }

        if ($errors === []) {
            try {
                $values = $result['values'];
                $userId = $users->registerCustomer([
                    'document' => (string) $values['document'],
                    'first_name' => (string) $values['first_name'],
                    'last_name' => (string) $values['last_name'],
                    'email' => (string) $values['email'],
                    'phone' => $values['phone'] === null ? null : (string) $values['phone'],
                    'password_hash' => (string) $values['password_hash'],
                ]);
                $this->app->auth()->login($userId);
                $this->app->csrf->rotate();

                return $this->app->redirect('/', 'success', '¡Bienvenido a Butaca! Ya puedes comprar tus boletos.');
            } catch (PDOException $e) {
                if (!DbErrorTranslator::isUserError($e)) {
                    throw $e;
                }
                $errors['_form'] = DbErrorTranslator::message($e);
            }
        }

        $old = $request->all();
        unset($old['password_hash'], $old['password_confirmation']);

        return $this->app->page('auth/register', [
            'pageTitle' => 'Crear cuenta',
            'old' => $old,
            'errors' => $errors,
        ], 422);
    }

    /** @param array<string, string> $params */
    public function logout(Request $request, array $params): Response
    {
        $this->app->auth()->logout();

        return $this->app->redirect('/', 'info', 'Cerraste sesión. ¡Vuelve pronto!');
    }

    /**
     * Only same-site absolute paths are accepted as post-login targets.
     */
    public static function safeNext(string $next): string
    {
        // One leading slash followed by printable ASCII only: rejects
        // "//host", "/\host", absolute URLs and control characters such as
        // TAB/CR/LF that browsers strip (turning "/<TAB>/host" into "//host").
        return preg_match('#^/(?![/\\\\])[\x21-\x7E]*$#', $next) === 1 ? $next : '/';
    }

    /**
     * Customers always start as "General"; discounted types (students,
     * seniors) are assigned by staff after checking their documents.
     */
    private static function registrationForm(): Resource
    {
        return new Resource(
            slug: 'registro',
            title: 'Registro',
            singular: 'cuenta',
            group: '',
            permission: '',
            table: 'app_user',
            keys: ['user_id'],
            listSource: 'app_user',
            columns: [],
            searchable: [],
            defaultSort: 'user_id',
            fields: [
                Field::text('document', 'Documento', 20, true, '^[0-9]{5,20}$', 'El documento debe tener solo números (5 a 20 dígitos).'),
                Field::text('first_name', 'Nombres', 60),
                Field::text('last_name', 'Apellidos', 60),
                Field::email('email', 'Correo'),
                Field::text('phone', 'Teléfono', 20, false, '^\+?[0-9 ]{7,20}$', 'Teléfono no válido.'),
                Field::password('password_hash', 'Contraseña'),
            ],
        );
    }
}
