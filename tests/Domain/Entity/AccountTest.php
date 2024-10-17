<?php declare(strict_types=1);

namespace Tests\Domain\Entity;

use App\Domain\Entity\Account;
use App\Domain\Entity\DifferentLedgerType;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class AccountTest extends TestCase {

    public function testCreditAmountToAccount(): void {
        $account = new Account(
            Uuid::uuid4(),
            1,
            1,
            0,
            0,
        );
        $account = $account->credit(1, 100);
        self::assertEquals(2, $account->getVersion());
        self::assertEquals(1, $account->getLedgerType());
        self::assertEquals(100, $account->getCreditAmount());
        self::assertEquals(0, $account->getDebitAmount());
        self::assertEquals($this->getNow(), $account->getDatetime());

    }

    public function testCreditAmountWithDifferentLedgerType(): void {
        $account = new Account(
            Uuid::uuid4(),
            1,
            1,
            0,
            0,
        );
        $this->expectException(DifferentLedgerType::class);
        $this->expectExceptionMessage('Different ledger type: 2 and 1');
        $account->credit(2, 100);
    }

    public function testDebitAmountToAccount(): void {
        $account = new Account(
            Uuid::uuid4(),
            1,
            1,
            0,
            0,
        );
        $account = $account->debit(1, 100);
        self::assertEquals(2, $account->getVersion());
        self::assertEquals(1, $account->getLedgerType());
        self::assertEquals(100, $account->getDebitAmount());
        self::assertEquals(0, $account->getCreditAmount());
        self::assertEquals($this->getNow(), $account->getDatetime());
    }

    public function testDebitAmountWithDifferentLedgerType(): void {
        $account = new Account(
            Uuid::uuid4(),
            1,
            1,
            0,
            0,
        );
        $this->expectException(DifferentLedgerType::class);
        $this->expectExceptionMessage('Different ledger type: 2 and 1');
        $account->debit(2, 100);
    }
}
