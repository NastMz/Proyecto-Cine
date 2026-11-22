<?php

declare(strict_types=1);

namespace Cine\Repository;

use PDO;

final class MovieRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Active movies with at least one upcoming showtime, plus the next one.
     *
     * @return list<array<string, mixed>>
     */
    public function nowShowing(?string $genre = null): array
    {
        $sql = 'SELECT m.movie_id, m.title, m.genre, m.duration_min, m.rating, m.release_date, m.poster_hue,
                       min(st.starts_at) AS next_showtime,
                       count(st.showtime_id)::int AS upcoming
                FROM movie m
                JOIN showtime st ON st.movie_id = m.movie_id AND st.starts_at > now()
                WHERE m.is_active';
        $params = [];
        if ($genre !== null && $genre !== '') {
            $sql .= ' AND m.genre = :genre';
            $params['genre'] = $genre;
        }
        $sql .= ' GROUP BY m.movie_id ORDER BY m.release_date DESC, m.title';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    /**
     * @return list<string>
     */
    public function genres(): array
    {
        return $this->pdo->query('SELECT DISTINCT genre FROM movie WHERE is_active ORDER BY genre')
            ->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $movieId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM movie WHERE movie_id = :id AND is_active');
        $stmt->execute(['id' => $movieId]);
        $movie = $stmt->fetch();

        return $movie === false ? null : $movie;
    }

    /**
     * Light-weight lookup used by the poster generator (inactive included).
     *
     * @return array<string, mixed>|null
     */
    public function posterData(int $movieId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT movie_id, title, genre, rating, poster_hue FROM movie WHERE movie_id = :id',
        );
        $stmt->execute(['id' => $movieId]);
        $movie = $stmt->fetch();

        return $movie === false ? null : $movie;
    }

    /**
     * @return list<string>
     */
    public function directors(int $movieId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT d.full_name FROM movie_director md
             JOIN director d ON d.director_id = md.director_id
             WHERE md.movie_id = :id ORDER BY d.full_name',
        );
        $stmt->execute(['id' => $movieId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * @return list<array{full_name: string, character_name: string}>
     */
    public function cast(int $movieId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.full_name, mc.character_name FROM movie_cast mc
             JOIN actor a ON a.actor_id = mc.actor_id
             WHERE mc.movie_id = :id ORDER BY mc.billing_order, a.full_name',
        );
        $stmt->execute(['id' => $movieId]);

        return $stmt->fetchAll();
    }
}
