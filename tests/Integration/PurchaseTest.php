<?php

declare(strict_types=1);

namespace Cine\Tests\Integration;

use Cine\Repository\PurchaseRepository;
use Cine\Repository\ShowtimeRepository;
use PDOException;

/**
 * The purchase flow implemented by fn_purchase_tickets, through the repository.
 */
final class PurchaseTest extends DatabaseTestCase
{
    private PurchaseRepository $purchases;
    private int $method;

    protected function setUp(): void
    {
        parent::setUp();
        $this->purchases = new PurchaseRepository($this->pdo);
        $this->method = (int) $this->scalar('SELECT min(payment_method_id) FROM payment_method');
    }

    public function testPurchaseCreatesTicketsAndTotal(): void
    {
        $st = $this->freeFutureShowtime();
        $user = $this->userId('andres@butaca.test');
        $seats = $this->seatsOf((int) $st['hall_id'], 3);

        $id = $this->purchases->purchase($user, (int) $st['showtime_id'], $seats, $this->method);

        $detail = $this->purchases->findForUser($id, $user);
        self::assertNotNull($detail);
        self::assertSame(3, $detail['seat_count']);
        $price = (new ShowtimeRepository($this->pdo))->priceFor((int) $st['showtime_id'], $user);
        self::assertEqualsWithDelta($price * 3, (float) $detail['total'], 0.001);
        self::assertSame(3, (int) $this->scalar('SELECT sold FROM v_showtime_availability WHERE showtime_id = :s', ['s' => $st['showtime_id']]));
    }

    public function testASeatCannotBeSoldTwice(): void
    {
        $st = $this->freeFutureShowtime();
        $seats = $this->seatsOf((int) $st['hall_id'], 2);
        $this->purchases->purchase($this->userId('andres@butaca.test'), (int) $st['showtime_id'], $seats, $this->method);

        self::assertSame('CN001', $this->failingPurchase($this->userId('laura@butaca.test'), (int) $st['showtime_id'], [$seats[1]]));
    }

    public function testStartedShowtimesAreClosed(): void
    {
        $past = $this->pdo->query('SELECT showtime_id, hall_id FROM showtime WHERE starts_at < now() LIMIT 1')->fetch();

        self::assertSame('CN002', $this->failingPurchase(
            $this->userId('andres@butaca.test'),
            (int) $past['showtime_id'],
            $this->seatsOf((int) $past['hall_id'], 1),
        ));
    }

    public function testSeatsFromAnotherHallAreRejected(): void
    {
        $st = $this->freeFutureShowtime();
        $otherHall = (int) $this->scalar('SELECT min(hall_id) FROM hall WHERE hall_id <> :h', ['h' => $st['hall_id']]);

        self::assertSame('CN003', $this->failingPurchase(
            $this->userId('andres@butaca.test'),
            (int) $st['showtime_id'],
            $this->seatsOf($otherHall, 1),
        ));
    }

    public function testEmptyAndOversizedSelectionsAreRejected(): void
    {
        $st = $this->freeFutureShowtime();
        $user = $this->userId('andres@butaca.test');

        self::assertSame('CN003', $this->failingPurchase($user, (int) $st['showtime_id'], []));
        self::assertSame('CN003', $this->failingPurchase($user, (int) $st['showtime_id'], $this->seatsOf((int) $st['hall_id'], 11)));
    }

    public function testInactiveUsersCannotBuy(): void
    {
        $st = $this->freeFutureShowtime();
        $user = $this->userId('marta@butaca.test');
        $this->pdo->exec("UPDATE app_user SET is_active = false WHERE user_id = {$user}");

        self::assertSame('CN004', $this->failingPurchase($user, (int) $st['showtime_id'], $this->seatsOf((int) $st['hall_id'], 1)));
    }

    public function testCustomerHistoryOnlyContainsOwnPurchases(): void
    {
        $user = $this->userId('laura@butaca.test');
        $rows = $this->purchases->forUser($user);

        self::assertNotEmpty($rows);
        foreach ($rows as $row) {
            self::assertSame($user, $row['user_id']);
        }
        $foreign = (int) $this->scalar('SELECT purchase_id FROM purchase WHERE user_id <> :u LIMIT 1', ['u' => $user]);
        self::assertNull($this->purchases->findForUser($foreign, $user));
    }

    /**
     * @param list<int> $seats
     */
    private function failingPurchase(int $user, int $showtime, array $seats): ?string
    {
        $this->pdo->exec('SAVEPOINT expect_failure');
        try {
            $this->purchases->purchase($user, $showtime, $seats, $this->method);
        } catch (PDOException $e) {
            $this->pdo->exec('ROLLBACK TO SAVEPOINT expect_failure');

            return $e->errorInfo[0] ?? null;
        }
        $this->pdo->exec('RELEASE SAVEPOINT expect_failure');

        return null;
    }
}
