<?php declare(strict_types=1);

namespace Tests\Domain;

use App\Domain\Account;
use App\Domain\Money;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

class AccountTest extends TestCase {

    public function testCreditAmountToAccount(): void {
        $account = new Account(
            Uuid::uuid4(),
            1,
            new Money(0, 1),
            new Money(0, 1),
        );
        $account = $account->credit(new Money(100, 1));
        self::assertEquals(2, $account->getSequence());
        self::assertEquals(100, $account->getCreditAmount()->getAmount());
        self::assertEquals(0, $account->getDebitAmount()->getAmount());
        self::assertEquals($this->getNow(), $account->getDatetime());

    }

    public function testDebitAmountToAccount(): void {
        $account = new Account(
            Uuid::uuid4(),
            1,
            new Money(0, 1),
            new Money(0, 1),
        );
        $account = $account->debit(new Money(100, 1));
        self::assertEquals(2, $account->getSequence());
        self::assertEquals(100, $account->getDebitAmount()->getAmount());
        self::assertEquals(0, $account->getCreditAmount()->getAmount());
        self::assertEquals($this->getNow(), $account->getDatetime());
    }
}
