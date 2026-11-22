<?php

declare(strict_types=1);

namespace Cine\Repository;

use Cine\Database\Database;
use PDO;

final class UserRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Returns the user with its password hash, or null.
     *
     * @return array<string, mixed>|null
     */
    public function findCredentials(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT u.user_id, u.password_hash, u.is_active
             FROM app_user u
             WHERE lower(u.email) = lower(:email)',
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findProfile(int $userId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM v_user_detail WHERE user_id = :id AND is_active');
        $stmt->execute(['id' => $userId]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    /**
     * @return list<string>
     */
    public function permissions(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.code
             FROM app_user u
             JOIN role r ON r.role_id = u.role_id AND r.is_active
             JOIN role_permission rp ON rp.role_id = r.role_id
             JOIN permission p ON p.permission_id = rp.permission_id
             WHERE u.user_id = :id
             ORDER BY p.code',
        );
        $stmt->execute(['id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function updatePasswordHash(int $userId, string $hash): void
    {
        $stmt = $this->pdo->prepare('UPDATE app_user SET password_hash = :hash WHERE user_id = :id');
        $stmt->execute(['hash' => $hash, 'id' => $userId]);
    }

    /**
     * Registers a customer ("cliente" role, lowest-discount user type) and
     * returns the new id.
     *
     * @param array{document: string, first_name: string, last_name: string, email: string, phone: ?string, password_hash: string} $data
     */
    public function registerCustomer(array $data): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO app_user (document, first_name, last_name, email, phone, password_hash, role_id, user_type_id)
             VALUES (:document, :first_name, :last_name, :email, :phone, :password_hash,
                     (SELECT role_id FROM role WHERE code = 'cliente'),
                     (SELECT user_type_id FROM user_type ORDER BY discount_pct, user_type_id LIMIT 1))
             RETURNING user_id",
        );

        return Database::atomic($this->pdo, static function () use ($stmt, $data): int {
            $stmt->execute($data);

            return (int) $stmt->fetchColumn();
        });
    }

    /**
     * @return list<array{user_type_id: int, name: string, discount_pct: string}>
     */
    public function userTypes(): array
    {
        return $this->pdo->query('SELECT user_type_id, name, discount_pct FROM user_type ORDER BY user_type_id')
            ->fetchAll();
    }
}
