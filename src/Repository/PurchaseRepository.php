<?php

declare(strict_types=1);

namespace Cine\Repository;

use Cine\Database\Database;
use PDO;

final class PurchaseRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Delegates the whole purchase to the `fn_purchase_tickets` stored
     * function, which validates and inserts atomically.
     *
     * @param list<int> $seatIds
     * @throws \PDOException with SQLSTATE CN00x on business-rule violations
     */
    public function purchase(int $userId, int $showtimeId, array $seatIds, int $paymentMethodId): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT fn_purchase_tickets(:user, :showtime, CAST(:seats AS integer[]), CAST(:method AS smallint))',
        );
        $params = [
            'user' => $userId,
            'showtime' => $showtimeId,
            'seats' => '{' . implode(',', array_map('intval', $seatIds)) . '}',
            'method' => $paymentMethodId,
        ];

        return Database::atomic($this->pdo, static function () use ($stmt, $params): int {
            $stmt->execute($params);

            return (int) $stmt->fetchColumn();
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findForUser(int $purchaseId, int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM v_purchase_detail WHERE purchase_id = :id AND user_id = :user');
        $stmt->execute(['id' => $purchaseId, 'user' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function forUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT *, starts_at > now() AS upcoming FROM v_purchase_detail
             WHERE user_id = :user
             ORDER BY (starts_at > now()) DESC,
                      CASE WHEN starts_at > now() THEN starts_at END,
                      starts_at DESC',
        );
        $stmt->execute(['user' => $userId]);

        return $stmt->fetchAll();
    }

    /**
     * @return list<array{payment_method_id: int, name: string}>
     */
    public function paymentMethods(): array
    {
        return $this->pdo->query(
            'SELECT payment_method_id, name FROM payment_method WHERE is_active ORDER BY payment_method_id',
        )->fetchAll();
    }
}
