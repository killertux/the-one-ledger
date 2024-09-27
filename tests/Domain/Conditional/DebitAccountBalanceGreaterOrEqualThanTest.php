<?php declare(strict_types=1);

namespace Tests\Domain\Conditional;

use App\Domain\Account;
use App\Domain\Conditional\DebitAccountBalanceGreaterOrEqualThan;
use App\Domain\Money;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class DebitAccountBalanceGreaterOrEqualThanTest extends TestCase {

    public function testCheck(): void {
        $conditional = new DebitAccountBalanceGreaterOrEqualThan(100);
        self::assertTrue(
            $conditional->check($this->createAccount(new Money(0, 1), new Money(100, 1)), $this->createAccount())
        );
        self::assertTrue(
            $conditional->check($this->createAccount(new Money(100, 1), new Money(200, 1)), $this->createAccount())
        );
        self::assertFalse(
            $conditional->check($this->createAccount(new Money(101, 1), new Money(200, 1)), $this->createAccount())
        );
        self::assertFalse(
            $conditional->check($this->createAccount(new Money(0, 1), new Money(99, 1)), $this->createAccount())
        );
    }

    public function testFailMessage(): void {
        $conditional = new DebitAccountBalanceGreaterOrEqualThan(100);
        self::assertEquals('Debit account balance would be less than 100', $conditional->failMessage());
    }

    private function createAccount(Money $debit_amount = null, Money $credit_amount = null): Account {
        return new Account(
            Uuid::uuid4(),
            1,
            $debit_amount ?? new Money(0, 1),
            $credit_amount ?? new Money(0, 1)
        );
    }
}
