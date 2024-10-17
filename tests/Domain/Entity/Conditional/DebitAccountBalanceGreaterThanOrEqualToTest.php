<?php declare(strict_types=1);

namespace Tests\Domain\Entity\Conditional;

use App\Domain\Entity\Account;
use App\Domain\Entity\Conditional\DebitAccountBalanceGreaterThanOrEqualTo;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class DebitAccountBalanceGreaterThanOrEqualToTest extends TestCase {

    public function testCheck(): void {
        $conditional = new DebitAccountBalanceGreaterThanOrEqualTo(100);
        self::assertTrue(
            $conditional->check($this->createAccount(0, 100), $this->createAccount())
        );
        self::assertTrue(
            $conditional->check($this->createAccount(100, 200), $this->createAccount())
        );
        self::assertFalse(
            $conditional->check($this->createAccount(101, 200), $this->createAccount())
        );
        self::assertFalse(
            $conditional->check($this->createAccount(0, 99), $this->createAccount())
        );
    }

    public function testFailMessage(): void {
        $conditional = new DebitAccountBalanceGreaterThanOrEqualTo(100);
        self::assertEquals('Debit account balance would be less than 100', $conditional->failMessage());
    }

    private function createAccount(int $debit_amount = null, int $credit_amount = null): Account {
        return new Account(
            Uuid::uuid4(),
            1,
            1,
            $debit_amount ?? 0,
            $credit_amount ?? 0
        );
    }
}
