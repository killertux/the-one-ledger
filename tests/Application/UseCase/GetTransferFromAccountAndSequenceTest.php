<?php declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Application\UseCase\GetTransferFromAccountAndSequence;
use App\Domain\Money;
use App\Infra\Repository\Transfer\TransferNotFound;
use Tests\Support\TransferUtils;
use Tests\TestCase;

class GetTransferFromAccountAndSequenceTest extends TestCase {
    use TransferUtils;

    public function testGetTransferFromCreditAccount(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $transfer_id = $this->createTransfer($debit_account_id, $credit_account_id);
        $response = (new GetTransferFromAccountAndSequence($this->getTransferRepository()))
            ->executeForCreditAccount($credit_account_id, 1);

        self::assertEquals(new TransferDto(
            $transfer_id,
            $debit_account_id,
            1,
            $credit_account_id,
            1,
            new Money(100, 1),
            (object)[],
            $this->getNow(),
        ), $response);
    }

    public function testGetTransferFromDebitAccount(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $transfer_id = $this->createTransfer($debit_account_id, $credit_account_id);
        $response = (new GetTransferFromAccountAndSequence($this->getTransferRepository()))
            ->executeForDebitAccount($debit_account_id, 1);

        self::assertEquals(new TransferDto(
            $transfer_id,
            $debit_account_id,
            1,
            $credit_account_id,
            1,
            new Money(100, 1),
            (object)[],
            $this->getNow(),
        ), $response);
    }

    public function testGetTransferFromCreditAccount_NotFound(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $this->createTransfer($debit_account_id, $credit_account_id);

        $this->expectException(TransferNotFound::class);
        $this->expectExceptionMessage("Transfer not found for credit account and sequence: $debit_account_id, 1");
        (new GetTransferFromAccountAndSequence($this->getTransferRepository()))
            ->executeForCreditAccount($debit_account_id, 1);
    }

    public function testGetTransferFromDebitAccount_NotFound(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $this->createTransfer($debit_account_id, $credit_account_id);

        $this->expectException(TransferNotFound::class);
        $this->expectExceptionMessage("Transfer not found for debit account and sequence: $credit_account_id, 1");
        (new GetTransferFromAccountAndSequence($this->getTransferRepository()))
            ->executeForDebitAccount($credit_account_id, 1);
    }

}
