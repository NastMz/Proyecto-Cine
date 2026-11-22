<?php

declare(strict_types=1);

namespace Cine\Repository;

use PDO;

/**
 * Read-only queries for the administration dashboard (built on the views).
 */
final class StatsRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{revenue_7d: string, tickets_7d: int, customers: int, upcoming: int, occupancy: ?string}
     */
    public function kpis(): array
    {
        return $this->pdo->query(
            "SELECT
                (SELECT COALESCE(sum(total), 0) FROM purchase WHERE purchased_at > now() - interval '7 days') AS revenue_7d,
                (SELECT count(*)::int FROM ticket t JOIN purchase p ON p.purchase_id = t.purchase_id
                  WHERE p.purchased_at > now() - interval '7 days') AS tickets_7d,
                (SELECT count(*)::int FROM app_user u JOIN role r ON r.role_id = u.role_id WHERE r.code = 'cliente') AS customers,
                (SELECT count(*)::int FROM showtime WHERE starts_at > now()) AS upcoming,
                (SELECT round(avg(occupancy_pct), 1) FROM v_showtime_availability
                  WHERE starts_at BETWEEN now() - interval '7 days' AND now()) AS occupancy",
        )->fetch();
    }

    /**
     * Revenue for each of the last `$days` days, zero-filled.
     *
     * @return list<array{sale_date: string, revenue: string, purchases: int}>
     */
    public function dailyRevenue(int $days = 7): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT d::date AS sale_date, COALESCE(r.revenue, 0) AS revenue, COALESCE(r.purchases, 0) AS purchases
             FROM generate_series(CURRENT_DATE - (:days - 1), CURRENT_DATE, interval '1 day') AS d
             LEFT JOIN v_daily_revenue r ON r.sale_date = d::date
             ORDER BY d",
        );
        $stmt->bindValue('days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function topMovies(int $limit = 5): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM v_movie_sales ORDER BY revenue DESC, title LIMIT :limit');
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function nextShowtimes(int $limit = 6): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM v_showtime_availability WHERE starts_at > now() ORDER BY starts_at, hall_name LIMIT :limit',
        );
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
