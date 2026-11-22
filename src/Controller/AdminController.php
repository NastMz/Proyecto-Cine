<?php

declare(strict_types=1);

namespace Cine\Controller;

use Cine\Admin\FormProcessor;
use Cine\Admin\Resource;
use Cine\App;
use Cine\Database\DbErrorTranslator;
use Cine\Http\Request;
use Cine\Http\Response;
use PDOException;

/**
 * Administration panel: dashboard and generic CRUD screens driven by the
 * {@see \Cine\Admin\ResourceRegistry}. Every action checks a permission.
 */
final class AdminController
{
    public function __construct(private readonly App $app)
    {
    }

    /** @param array<string, string> $params */
    public function dashboard(Request $request, array $params): Response
    {
        if (($denied = $this->guard('admin.access', $request)) !== null) {
            return $denied;
        }
        $stats = $this->app->stats();

        return $this->page('admin/dashboard', [
            'pageTitle' => 'Panel',
            'kpis' => $stats->kpis(),
            'daily' => $stats->dailyRevenue(7),
            'topMovies' => $stats->topMovies(5),
            'nextShowtimes' => $stats->nextShowtimes(6),
        ]);
    }

    /** @param array<string, string> $params */
    public function index(Request $request, array $params): Response
    {
        [$resource, $denied] = $this->resolve($params, $request);
        if ($resource === null) {
            return $denied;
        }

        $result = $this->app->crud()->paginate(
            $resource,
            $request->query('q'),
            $request->query('orden'),
            $request->query('dir'),
            max(1, (int) $request->query('pagina', '1')),
        );

        return $this->page('admin/index', [
            'pageTitle' => $resource->title,
            'resource' => $resource,
            'result' => $result,
            'search' => $request->query('q'),
        ]);
    }

    /** @param array<string, string> $params */
    public function create(Request $request, array $params): Response
    {
        [$resource, $denied] = $this->resolve($params, $request);
        if ($resource === null) {
            return $denied;
        }
        if ($resource->isReadOnly()) {
            return $this->app->error(404, 'No disponible', 'Este módulo es de solo lectura.');
        }

        return $this->form($resource, null, $this->defaults($resource), []);
    }

    /** @param array<string, string> $params */
    public function store(Request $request, array $params): Response
    {
        [$resource, $denied] = $this->resolve($params, $request);
        if ($resource === null) {
            return $denied;
        }
        if ($resource->isReadOnly()) {
            return $this->app->error(404, 'No disponible', 'Este módulo es de solo lectura.');
        }

        $crud = $this->app->crud();
        $result = (new FormProcessor())->process($resource, $request->all(), false, $crud->relationOptions($resource));
        if ($result['errors'] === []) {
            try {
                $crud->insert($resource, $result['values']);

                return $this->app->redirect($this->url($resource), 'success', 'Registro creado correctamente.');
            } catch (PDOException $e) {
                $result['errors']['_form'] = $this->dbError($e);
            }
        }

        return $this->form($resource, null, $request->all(), $result['errors'], 422);
    }

    /** @param array<string, string> $params */
    public function edit(Request $request, array $params): Response
    {
        [$resource, $denied] = $this->resolve($params, $request);
        if ($resource === null) {
            return $denied;
        }
        $key = $resource->canEdit() ? $resource->parseKey($params['key']) : null;
        $row = $key === null ? null : $this->app->crud()->find($resource, $key);
        if ($row === null) {
            return $this->app->error(404, 'Registro no encontrado', 'El registro no existe o no se puede editar.');
        }
        unset($row['password_hash']);

        return $this->form($resource, $params['key'], $row, []);
    }

    /** @param array<string, string> $params */
    public function update(Request $request, array $params): Response
    {
        [$resource, $denied] = $this->resolve($params, $request);
        if ($resource === null) {
            return $denied;
        }
        $key = $resource->canEdit() ? $resource->parseKey($params['key']) : null;
        $crud = $this->app->crud();
        if ($key === null || $crud->find($resource, $key) === null) {
            return $this->app->error(404, 'Registro no encontrado', 'El registro no existe o no se puede editar.');
        }

        $result = (new FormProcessor())->process($resource, $request->all(), true, $crud->relationOptions($resource));
        if ($result['errors'] === []) {
            try {
                $crud->update($resource, $key, $result['values']);

                return $this->app->redirect($this->url($resource), 'success', 'Cambios guardados.');
            } catch (PDOException $e) {
                $result['errors']['_form'] = $this->dbError($e);
            }
        }

        return $this->form($resource, $params['key'], $request->all(), $result['errors'], 422);
    }

    /** @param array<string, string> $params */
    public function destroy(Request $request, array $params): Response
    {
        [$resource, $denied] = $this->resolve($params, $request);
        if ($resource === null) {
            return $denied;
        }
        $key = $resource->isReadOnly() ? null : $resource->parseKey($params['key']);
        if ($key === null) {
            return $this->app->error(404, 'Registro no encontrado', 'El registro no existe.');
        }
        if ($resource->table === 'app_user' && $key['user_id'] === $this->app->auth()->id()) {
            return $this->app->redirect($this->url($resource), 'error', 'No puedes eliminar tu propia cuenta.');
        }

        try {
            $deleted = $this->app->crud()->delete($resource, $key);
        } catch (PDOException $e) {
            return $this->app->redirect($this->url($resource), 'error', $this->dbError($e));
        }

        return $this->app->redirect(
            $this->url($resource),
            $deleted ? 'success' : 'error',
            $deleted ? 'Registro eliminado.' : 'El registro ya no existía.',
        );
    }

    /**
     * @return array{0: ?Resource, 1: Response}
     */
    private function resolve(array $params, Request $request): array
    {
        $resource = $this->app->resources->get($params['resource'] ?? '');
        if ($resource === null) {
            return [null, $this->app->error(404, 'Módulo no encontrado', 'Ese módulo no existe.')];
        }
        $denied = $this->guard('admin.access', $request) ?? $this->guard($resource->permission, $request);

        return $denied === null ? [$resource, new Response()] : [null, $denied];
    }

    private function guard(string $permission, Request $request): ?Response
    {
        $auth = $this->app->auth();
        if (!$auth->check()) {
            return $this->app->redirect('/ingresar?next=' . rawurlencode($request->path), 'info', 'Ingresa con una cuenta de personal.');
        }
        if (!$auth->can($permission)) {
            return $this->app->error(403, 'Acceso restringido', 'Tu rol no tiene permiso para ver esta sección.');
        }

        return null;
    }

    /**
     * @param array<string, mixed> $values
     * @param array<string, string> $errors
     */
    private function form(Resource $resource, ?string $key, array $values, array $errors, int $status = 200): Response
    {
        return $this->page('admin/form', [
            'pageTitle' => ($key === null ? 'Agregar ' : 'Editar ') . $resource->singular,
            'resource' => $resource,
            'key' => $key,
            'values' => $values,
            'errors' => $errors,
            'options' => $this->app->crud()->relationOptions($resource),
        ], $status);
    }

    /**
     * @return array<string, mixed>
     */
    private function defaults(Resource $resource): array
    {
        $values = [];
        foreach ($resource->fields as $field) {
            if ($field->type === \Cine\Admin\Field::BOOL) {
                $values[$field->name] = true;
            }
        }

        return $values;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function page(string $template, array $data, int $status = 200): Response
    {
        $data['nav'] = $this->app->resources->grouped();

        return $this->app->page($template, $data, $status, 'layouts/admin');
    }

    private function url(Resource $resource): string
    {
        return '/admin/' . $resource->slug;
    }

    private function dbError(PDOException $e): string
    {
        if (!DbErrorTranslator::isUserError($e)) {
            throw $e;
        }

        return DbErrorTranslator::message($e);
    }
}
