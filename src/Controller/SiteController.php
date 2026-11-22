<?php

declare(strict_types=1);

namespace Cine\Controller;

use Cine\App;
use Cine\Http\Request;
use Cine\Http\Response;
use Cine\Poster\PosterGenerator;
use Cine\Support\Format;

/**
 * Public pages: billboard ("cartelera"), movie detail and generated posters.
 */
final class SiteController
{
    public function __construct(private readonly App $app)
    {
    }

    /** @param array<string, string> $params */
    public function home(Request $request, array $params): Response
    {
        $movies = $this->app->movies();
        $genre = $request->query('genero');
        $genres = $movies->genres();
        if (!in_array($genre, $genres, true)) {
            $genre = '';
        }
        $list = $movies->nowShowing($genre);

        return $this->app->page('site/home', [
            'pageTitle' => 'Cartelera',
            'movies' => $list,
            'featured' => $genre === '' ? ($list[0] ?? null) : null,
            'genres' => $genres,
            'genre' => $genre,
        ]);
    }

    /** @param array<string, string> $params */
    public function movie(Request $request, array $params): Response
    {
        $movieId = (int) $params['id'];
        $movie = $this->app->movies()->find($movieId);
        if ($movie === null) {
            return $this->app->error(404, 'Película no encontrada', 'Esta película no está en cartelera.');
        }

        $byDay = [];
        foreach ($this->app->showtimes()->upcomingForMovie($movieId) as $showtime) {
            $day = Format::date($showtime['starts_at'])->format('Y-m-d');
            $byDay[$day][] = $showtime;
        }

        return $this->app->page('site/movie', [
            'pageTitle' => $movie['title'],
            'movie' => $movie,
            'directors' => $this->app->movies()->directors($movieId),
            'cast' => $this->app->movies()->cast($movieId),
            'showtimesByDay' => $byDay,
        ]);
    }

    /** @param array<string, string> $params */
    public function poster(Request $request, array $params): Response
    {
        $movie = $this->app->movies()->posterData((int) $params['id']);
        if ($movie === null) {
            return new Response('', 404);
        }

        return Response::svg((new PosterGenerator())->render($movie));
    }
}
