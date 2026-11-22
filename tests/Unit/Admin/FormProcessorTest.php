<?php

declare(strict_types=1);

namespace Cine\Tests\Unit\Admin;

use Cine\Admin\Field;
use Cine\Admin\FormProcessor;
use Cine\Admin\Resource;
use Cine\Admin\ResourceRegistry;
use PHPUnit\Framework\TestCase;

final class FormProcessorTest extends TestCase
{
    private FormProcessor $form;

    protected function setUp(): void
    {
        $this->form = new FormProcessor();
    }

    public function testValidMovieIsNormalized(): void
    {
        $result = $this->form->process($this->movies(), [
            'title' => '  La prueba  ',
            'original_title' => '',
            'synopsis' => 'Texto',
            'genre' => 'Drama',
            'duration_min' => '120',
            'rating' => '12+',
            'release_date' => '2026-01-31',
            'poster_hue' => '200',
            'is_active' => '1',
            'evil_column' => 'DROP TABLE movie',
        ], false);

        self::assertSame([], $result['errors']);
        self::assertSame('La prueba', $result['values']['title']);
        self::assertNull($result['values']['original_title']);
        self::assertSame(120, $result['values']['duration_min']);
        self::assertTrue($result['values']['is_active']);
        self::assertArrayNotHasKey('evil_column', $result['values']);
    }

    public function testReportsEveryInvalidField(): void
    {
        $result = $this->form->process($this->movies(), [
            'title' => str_repeat('x', 121),
            'synopsis' => '',
            'genre' => 'Drama',
            'duration_min' => '0',
            'rating' => 'R',
            'release_date' => '2026-02-30',
            'poster_hue' => '12.5',
        ], false);

        self::assertSame(
            ['title', 'synopsis', 'duration_min', 'rating', 'release_date', 'poster_hue'],
            array_keys($result['errors']),
        );
        self::assertSame('Máximo 120 caracteres.', $result['errors']['title']);
        self::assertSame('Este campo es obligatorio.', $result['errors']['synopsis']);
    }

    public function testMissingCheckboxMeansFalse(): void
    {
        $resource = new Resource('x', 'X', 'x', 'g', 'p', 't', ['id'], 't', [], [], 'id', [Field::bool('is_active', 'Activo')]);

        self::assertFalse($this->form->process($resource, [], false)['values']['is_active']);
        self::assertFalse($this->form->process($resource, ['is_active' => '0'], false)['values']['is_active']);
    }

    public function testPasswordIsHashedAndOptionalOnUpdate(): void
    {
        $users = (new ResourceRegistry())->get('usuarios');
        $input = ['password_hash' => 'secreta-123'];

        $created = $this->form->process($users, $input, false);
        self::assertTrue(password_verify('secreta-123', $created['values']['password_hash']));

        $updated = $this->form->process($users, ['password_hash' => ''], true);
        self::assertArrayNotHasKey('password_hash', $updated['values']);
        self::assertArrayNotHasKey('password_hash', $updated['errors']);

        self::assertArrayHasKey('password_hash', $this->form->process($users, ['password_hash' => 'corta'], true)['errors']);
    }

    public function testRelationValuesMustBeAmongTheOptions(): void
    {
        $resource = (new ResourceRegistry())->get('dirige');
        $options = ['movie_id' => [1 => 'A', 2 => 'B'], 'director_id' => [7 => 'D']];

        $ok = $this->form->process($resource, ['movie_id' => '2', 'director_id' => '7'], false, $options);
        self::assertSame(['movie_id' => 2, 'director_id' => 7], $ok['values']);

        $bad = $this->form->process($resource, ['movie_id' => '99', 'director_id' => '7'], false, $options);
        self::assertSame('Selecciona una opción válida.', $bad['errors']['movie_id']);
    }

    public function testDateTimeEmailDecimalAndPatternRules(): void
    {
        $resource = new Resource('x', 'X', 'x', 'g', 'p', 't', ['id'], 't', [], [], 'id', [
            Field::dateTime('starts_at', 'Inicio'),
            Field::email('email', 'Correo'),
            Field::decimal('price', 'Precio', 0, 100),
            Field::text('document', 'Documento', 20, true, '^[0-9]{5,20}$'),
        ]);

        $ok = $this->form->process($resource, [
            'starts_at' => '2026-12-01T19:30',
            'email' => 'Ana@Example.TEST',
            'price' => '12,5',
            'document' => '123456',
        ], false);
        self::assertSame([], $ok['errors']);
        self::assertSame('2026-12-01 19:30:00', $ok['values']['starts_at']);
        self::assertSame('ana@example.test', $ok['values']['email']);
        self::assertSame(12.5, $ok['values']['price']);

        $bad = $this->form->process($resource, [
            'starts_at' => 'mañana',
            'email' => 'no-es-correo',
            'price' => '101',
            'document' => '12ab',
        ], false);
        self::assertSame(['starts_at', 'email', 'price', 'document'], array_keys($bad['errors']));
    }

    private function movies(): Resource
    {
        return (new ResourceRegistry())->get('peliculas');
    }
}
