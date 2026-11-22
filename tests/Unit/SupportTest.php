<?php

declare(strict_types=1);

namespace Cine\Tests\Unit;

use Cine\Config;
use Cine\Database\DbErrorTranslator;
use Cine\Poster\PosterGenerator;
use Cine\Support\Format;
use Cine\View\View;
use PDOException;
use PHPUnit\Framework\TestCase;

final class SupportTest extends TestCase
{
    public function testMoneyAndDurations(): void
    {
        self::assertSame("$\u{00A0}14.000", Format::money(14000));
        self::assertSame("$\u{00A0}1.234.568", Format::money('1234567.5'));
        self::assertSame('1 h 58 min', Format::duration(118));
        self::assertSame('45 min', Format::duration(45));
    }

    public function testSpanishDates(): void
    {
        self::assertSame('viernes 27 de noviembre', Format::longDay('2026-11-27 12:00:00-05'));
        self::assertSame('vie 27 nov', Format::shortDay('2026-11-27 12:00:00-05'));
        self::assertSame("7:00\u{00A0}p.\u{00A0}m.", Format::time('2026-11-28 00:00:00+00'));
        self::assertSame("9:30\u{00A0}a.\u{00A0}m.", Format::time('2026-11-27 09:30:00-05'));
    }

    public function testEscapeHelper(): void
    {
        self::assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', e('<script>alert("x")</script>'));
        self::assertSame('&apos; onmouseover=&apos;', e("' onmouseover='"));
        self::assertSame('', e(null));
        self::assertSame('42', e(42));
    }

    public function testPosterEscapesTextAndWrapsTitles(): void
    {
        $generator = new PosterGenerator();
        $svg = $generator->render([
            'movie_id' => 1,
            'title' => 'Tom & Jerry <script>',
            'genre' => 'Comedia "rara"',
            'rating' => '7+',
            'poster_hue' => 400,
        ]);

        self::assertStringNotContainsString('<script>', $svg);
        self::assertStringContainsString('TOM &amp; JERRY', $svg);
        self::assertStringContainsString('Comedia &quot;rara&quot;', $svg);
        self::assertNotFalse(simplexml_load_string($svg), 'poster must be well-formed XML');

        self::assertSame(['LA ÚLTIMA', 'FUNCIÓN'], $generator->wrap('LA ÚLTIMA FUNCIÓN', 12));
        self::assertLessThan($generator->fontSize(['ABC']), $generator->fontSize(['MEDIANOCHE']));
    }

    public function testDbErrorsAreTranslated(): void
    {
        $overlap = $this->pdoError('23P01', 'conflicting key value violates exclusion constraint "showtime_no_overlap_ex"');
        self::assertSame('La sala ya tiene una función que se cruza con ese horario.', DbErrorTranslator::message($overlap));

        self::assertSame('Alguna de las sillas elegidas ya fue vendida. Elige otras.', DbErrorTranslator::message($this->pdoError('CN001', 'x')));
        self::assertTrue(DbErrorTranslator::isUserError($this->pdoError('23503', 'fk')));
        self::assertFalse(DbErrorTranslator::isUserError($this->pdoError('08006', 'connection lost')));
    }

    public function testConfigReadsEnvironmentAndDotEnv(): void
    {
        $config = Config::fromArray(['APP_DEBUG' => 'true', 'DB_PORT' => '8141', 'DB_PASSWORD' => 's3cret']);
        self::assertTrue($config->debug);
        self::assertSame(8141, $config->dbPort);
        self::assertSame('cine', $config->dbName);
        self::assertSame('other', $config->withDatabase('other')->dbName);

        $file = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($file, "# comment\nDB_HOST=db\nDB_PASSWORD=\"quoted\"\nINVALID\n");
        self::assertSame(['DB_HOST' => 'db', 'DB_PASSWORD' => 'quoted'], Config::parseDotEnv($file));
        unlink($file);
    }

    public function testViewRendersTemplatesInsideLayoutsAndRejectsTraversal(): void
    {
        $view = new View(dirname(__DIR__, 2) . '/templates');
        $html = $view->render('errors/error', ['status' => 404, 'title' => '<i>X</i>', 'message' => 'm'], null);

        self::assertStringContainsString('&lt;i&gt;X&lt;/i&gt;', $html);

        $this->expectException(\RuntimeException::class);
        $view->partial('../composer');
    }

    private function pdoError(string $state, string $message): PDOException
    {
        $e = new PDOException($message);
        $e->errorInfo = [$state, 7, $message];

        return $e;
    }
}
