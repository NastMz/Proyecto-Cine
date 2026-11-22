<?php

declare(strict_types=1);

namespace Cine\Repository;

use Cine\Database\Database;
use PDO;

/**
 * Failed-login bookkeeping used by the brute-force throttle.
 */
final class LoginAttemptRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * @return array{email: int, ip: int} failures inside the window
     */
    public function recentFailures(string $email, string $ip, int $windowMinutes): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT
                count(*) FILTER (WHERE email = :email)::int AS email,
                count(*) FILTER (WHERE ip_address = :ip)::int AS ip
             FROM login_attempt
             WHERE attempted_at > now() - make_interval(mins => :window)
               AND (email = :email2 OR ip_address = :ip2)",
        );
        $stmt->bindValue('email', self::normalize($email));
        $stmt->bindValue('email2', self::normalize($email));
        $stmt->bindValue('ip', $ip);
        $stmt->bindValue('ip2', $ip);
        $stmt->bindValue('window', $windowMinutes, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function recordFailure(string $email, string $ip): void
    {
        $stmt = $this->pdo->prepare('INSERT INTO login_attempt (email, ip_address) VALUES (:email, :ip)');
        Database::atomic($this->pdo, function () use ($stmt, $email, $ip): void {
            $stmt->execute(['email' => self::normalize($email), 'ip' => mb_substr($ip, 0, 45)]);
            // Housekeeping: old rows are irrelevant for the throttle.
            $this->pdo->exec("DELETE FROM login_attempt WHERE attempted_at < now() - interval '1 day'");
        });
    }

    /** A successful login clears the account's failure counter. */
    public function clear(string $email): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM login_attempt WHERE email = :email');
        Database::atomic($this->pdo, static fn () => $stmt->execute(['email' => self::normalize($email)]));
    }

    private static function normalize(string $email): string
    {
        return mb_substr(mb_strtolower(trim($email)), 0, 120);
    }
}
