<?php

declare(strict_types=1);

namespace Cine\Tests\Unit\Admin;

use Cine\Admin\ResourceRegistry;
use PHPUnit\Framework\TestCase;

final class ResourceRegistryTest extends TestCase
{
    public function testEveryResourceIsConsistent(): void
    {
        $registry = new ResourceRegistry();
        $permissions = ['catalog.manage', 'users.manage', 'sales.view'];

        foreach ($registry->all() as $resource) {
            self::assertMatchesRegularExpression('/^[a-z-]+$/', $resource->slug);
            self::assertContains($resource->permission, $permissions, $resource->slug);
            self::assertArrayHasKey($resource->defaultSort, $resource->columns, $resource->slug);
            foreach ($resource->searchable as $column) {
                self::assertArrayHasKey($column, $resource->columns + ['original_title' => 1], $resource->slug);
            }
            foreach ($resource->fields as $field) {
                self::assertMatchesRegularExpression('/^[a-z_]+$/', $field->name, $resource->slug);
            }
        }
    }

    public function testGroupsFollowTheSidebarOrder(): void
    {
        self::assertSame(['Películas', 'Salas', 'Ventas', 'Usuarios'], array_keys((new ResourceRegistry())->grouped()));
    }

    public function testKeysRoundTripAndRejectMalformedInput(): void
    {
        $registry = new ResourceRegistry();
        $composite = $registry->get('permisos-rol');

        self::assertSame('3~5', $composite->keyOf(['role_id' => 3, 'permission_id' => 5, 'code' => 'x']));
        self::assertSame(['role_id' => 3, 'permission_id' => 5], $composite->parseKey('3~5'));
        self::assertNull($composite->parseKey('3'));
        self::assertNull($composite->parseKey('3~x'));
        self::assertSame(['movie_id' => 7], $registry->get('peliculas')->parseKey('7'));
    }

    public function testReadOnlyAndCreateOnlyResources(): void
    {
        $registry = new ResourceRegistry();

        self::assertTrue($registry->get('compras')->isReadOnly());
        self::assertTrue($registry->get('sillas')->isReadOnly());
        self::assertFalse($registry->get('dirige')->canEdit());
        self::assertTrue($registry->get('peliculas')->canEdit());
        self::assertNull($registry->get('no-existe'));
    }
}
