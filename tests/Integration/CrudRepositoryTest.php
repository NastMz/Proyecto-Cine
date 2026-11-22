<?php

declare(strict_types=1);

namespace Cine\Tests\Integration;

use Cine\Admin\CrudRepository;
use Cine\Admin\FormProcessor;
use Cine\Admin\ResourceRegistry;

final class CrudRepositoryTest extends DatabaseTestCase
{
    private CrudRepository $crud;
    private ResourceRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->crud = new CrudRepository($this->pdo);
        $this->registry = new ResourceRegistry();
    }

    public function testEveryResourceListLoads(): void
    {
        foreach ($this->registry->all() as $resource) {
            $page = $this->crud->paginate($resource);
            self::assertGreaterThan(0, $page['total'], $resource->slug . ' should have seed rows');
            self::assertLessThanOrEqual(CrudRepository::PER_PAGE, count($page['rows']));
        }
    }

    public function testSearchSortAndPaginationAreApplied(): void
    {
        $movies = $this->registry->get('peliculas');

        $result = $this->crud->paginate($movies, 'neón', 'title', 'desc');
        self::assertSame(1, $result['total']);
        self::assertSame('Ciudad de neón', $result['rows'][0]['title']);

        $unknownSort = $this->crud->paginate($movies, '', 'title; DROP TABLE movie', 'sideways', 99);
        self::assertSame('release_date', $unknownSort['sort']);
        self::assertSame('asc', $unknownSort['dir']);

        $sales = $this->crud->paginate($this->registry->get('compras'));
        self::assertSame('desc', $sales['dir']);
        self::assertGreaterThan($sales['rows'][1]['purchase_id'], $sales['rows'][0]['purchase_id']);
        self::assertSame(1, $unknownSort['page']);
    }

    public function testSearchTreatsWildcardsLiterally(): void
    {
        $result = $this->crud->paginate($this->registry->get('peliculas'), '%');

        self::assertSame(0, $result['total']);
    }

    public function testInsertUpdateAndDeleteRoundTrip(): void
    {
        $resource = $this->registry->get('directores');
        $form = new FormProcessor();

        $created = $form->process($resource, ['full_name' => 'Directora de Prueba', 'nationality' => '', 'birth_date' => '1980-02-29'], false);
        self::assertSame([], $created['errors']);
        $this->crud->insert($resource, $created['values']);
        $id = (int) $this->scalar("SELECT director_id FROM director WHERE full_name = 'Directora de Prueba'");
        self::assertNull($this->crud->find($resource, ['director_id' => $id])['nationality']);

        $this->crud->update($resource, ['director_id' => $id], ['nationality' => 'Uruguaya']);
        self::assertSame('Uruguaya', $this->crud->find($resource, ['director_id' => $id])['nationality']);

        self::assertTrue($this->crud->delete($resource, ['director_id' => $id]));
        self::assertNull($this->crud->find($resource, ['director_id' => $id]));
    }

    public function testCompositeKeyResource(): void
    {
        $resource = $this->registry->get('permisos-rol');
        $row = $this->crud->paginate($resource)['rows'][0];
        $key = $resource->parseKey($resource->keyOf($row));

        self::assertNotNull($this->crud->find($resource, $key));
        self::assertTrue($this->crud->delete($resource, $key));
        self::assertNull($this->crud->find($resource, $key));
    }

    public function testBooleanAndPasswordFieldsArePersisted(): void
    {
        $resource = $this->registry->get('usuarios');
        $options = $this->crud->relationOptions($resource);
        $result = (new FormProcessor())->process($resource, [
            'document' => '12345678',
            'first_name' => 'Nuevo',
            'last_name' => 'Usuario',
            'email' => 'Nuevo@Example.test',
            'phone' => '',
            'password_hash' => 'clave-segura-1',
            'role_id' => (string) array_key_first($options['role_id']),
            'user_type_id' => (string) array_key_first($options['user_type_id']),
            'is_active' => '0',
        ], false, $options);

        self::assertSame([], $result['errors']);
        $this->crud->insert($resource, $result['values']);
        $row = $this->pdo->query("SELECT * FROM app_user WHERE email = 'nuevo@example.test'")->fetch();

        self::assertFalse($row['is_active']);
        self::assertTrue(password_verify('clave-segura-1', $row['password_hash']));
    }
}
