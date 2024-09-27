<?php declare(strict_types=1);

namespace Tests\Domain\Entity;

use App\Domain\Entity\Account;
use App\Domain\Entity\Money;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class AccountTest extends TestCase {

    public function testCreditAmountToAccount(): void {
        $account = new Account(
            Uuid::uuid4(),
            1,
            new Money(0, 1),
            new Money(0, 1),
        );
        $account = $account->credit(new Money(100, 1));
        self::assertEquals(2, $account->getVersion());
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
        self::assertEquals(2, $account->getVersion());
        self::assertEquals(100, $account->getDebitAmount()->getAmount());
        self::assertEquals(0, $account->getCreditAmount()->getAmount());
        self::assertEquals($this->getNow(), $account->getDatetime());
    }
}
