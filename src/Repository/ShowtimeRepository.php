<?php

declare(strict_types=1);

namespace Cine\Repository;

use PDO;

final class ShowtimeRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Upcoming showtimes of a movie with availability, in start order.
     *
     * @return list<array<string, mixed>>
     */
    public function upcomingForMovie(int $movieId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM v_showtime_availability
             WHERE movie_id = :id AND starts_at > now()
             ORDER BY starts_at, hall_name',
        );
        $stmt->execute(['id' => $movieId]);

        return $stmt->fetchAll();
    }

    /**
     * @return array<string, mixed>|null
     */
    public function find(int $showtimeId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM v_showtime_availability WHERE showtime_id = :id');
        $stmt->execute(['id' => $showtimeId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * Seats of the showtime's hall with their sale status.
     *
     * @return list<array{seat_id: int, row_label: string, seat_number: int, seat_kind: string, sold: bool}>
     */
    public function seatMap(int $showtimeId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT s.seat_id, s.row_label, s.seat_number, s.seat_kind, (t.ticket_id IS NOT NULL) AS sold
             FROM showtime st
             JOIN seat s ON s.hall_id = st.hall_id
             LEFT JOIN ticket t ON t.showtime_id = st.showtime_id AND t.seat_id = s.seat_id
             WHERE st.showtime_id = :id
             ORDER BY s.row_label, s.seat_number',
        );
        $stmt->execute(['id' => $showtimeId]);

        return $stmt->fetchAll();
    }

    public function priceFor(int $showtimeId, ?int $userId): ?float
    {
        $stmt = $this->pdo->prepare('SELECT fn_ticket_price(:showtime, :user)');
        $stmt->bindValue('showtime', $showtimeId, PDO::PARAM_INT);
        $stmt->bindValue('user', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->execute();
        $price = $stmt->fetchColumn();

        return $price === null || $price === false ? null : (float) $price;
    }
}
