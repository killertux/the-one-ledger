<?php declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Application\UseCase\ListTransfers;
use Ramsey\Uuid\Uuid;
use Tests\Support\AccountUtils;
use Tests\Support\TransferUtils;
use Tests\TestCase;

class ListTransfersTest extends TestCase {
    use TransferUtils;
    use AccountUtils;

    public function testListTransfersFromCreditAccount(): void {
        $debit_account_id_1 = $this->createAccount();
        $debit_account_id_2 = $this->createAccount();
        $credit_account_id_1 = $this->createAccount();
        $credit_account_id_2 = $this->createAccount();
        $transfer_id_1 = $this->createTransfer($debit_account_id_1, $credit_account_id_1, 100);
        $transfer_id_2 = $this->createTransfer($debit_account_id_2, $credit_account_id_1, 200);
        $this->createTransfer($debit_account_id_2, $credit_account_id_2, 200);

        $response = (new ListTransfers($this->getTransferRepository()))
            ->executeFromCreditAccount($credit_account_id_1, 100);

        self::assertEquals(
            [
                new TransferDto(
                    $transfer_id_2,
                    $debit_account_id_2,
                    1,
                    $credit_account_id_1,
                    2,
                    1,
                    200,
                    (object)[],
                    $this->getNow(),
                ),
                new TransferDto(
                    $transfer_id_1,
                    $debit_account_id_1,
                    1,
                    $credit_account_id_1,
                    1,
                    1,
                    100,
                    (object)[],
                    $this->getNow(),
                ),
            ],
            $response
        );
    }

    public function testListTransfersFromCreditAccount_ShouldRespectLimitAndBeforeVersion(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $transfer_id_1 = $this->createTransfer($debit_account_id, $credit_account_id, 100);
        $transfer_id_2 = $this->createTransfer($debit_account_id, $credit_account_id, 200);
        $this->createTransfer($debit_account_id, $credit_account_id, 300);

        $response = (new ListTransfers($this->getTransferRepository()))
            ->executeFromCreditAccount($credit_account_id, 2, 3);

        self::assertEquals(
            [
                new TransferDto(
                    $transfer_id_2,
                    $debit_account_id,
                    2,
                    $credit_account_id,
                    2,
                    1,
                    200,
                    (object)[],
                    $this->getNow(),
                ),
                new TransferDto(
                    $transfer_id_1,
                    $debit_account_id,
                    1,
                    $credit_account_id,
                    1,
                    1,
                    100,
                    (object)[],
                    $this->getNow(),
                ),
            ],
            $response
        );
    }

    public function testListTransfersFromCreditAccount_LimitGreaterThan100_ShouldThrowException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');
        (new ListTransfers($this->getTransferRepository()))
            ->executeFromCreditAccount(Uuid::uuid4(), 101);
    }

    public function testListTransfersFromCreditAccount_LimitLessThan1_ShouldThrowException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');
        (new ListTransfers($this->getTransferRepository()))
            ->executeFromCreditAccount(Uuid::uuid4(), 0);
    }

    public function testListTransfersFromDebitAccount(): void {
        $debit_account_id_1 = $this->createAccount();
        $debit_account_id_2 = $this->createAccount();
        $credit_account_id_1 = $this->createAccount();
        $credit_account_id_2 = $this->createAccount();
        $transfer_id_1 = $this->createTransfer($debit_account_id_1, $credit_account_id_1, 100);
        $transfer_id_2 = $this->createTransfer($debit_account_id_1, $credit_account_id_2, 200);
        $this->createTransfer($debit_account_id_2, $credit_account_id_2, 200);

        $response = (new ListTransfers($this->getTransferRepository()))
            ->executeFromDebitAccount($debit_account_id_1, 100);

        self::assertEquals(
            [
                new TransferDto(
                    $transfer_id_2,
                    $debit_account_id_1,
                    2,
                    $credit_account_id_2,
                    1,
                    1,
                    200,
                    (object)[],
                    $this->getNow(),
                ),
                new TransferDto(
                    $transfer_id_1,
                    $debit_account_id_1,
                    1,
                    $credit_account_id_1,
                    1,
                    1,
                    100,
                    (object)[],
                    $this->getNow(),
                ),
            ],
            $response
        );
    }

    public function testListTransfersFromDebitAccount_ShouldRespectLimitAndBeforeVersion(): void {
        $debit_account_id = $this->createAccount();
        $credit_account_id = $this->createAccount();
        $transfer_id_1 = $this->createTransfer($debit_account_id, $credit_account_id, 100);
        $transfer_id_2 = $this->createTransfer($debit_account_id, $credit_account_id, 200);
        $this->createTransfer($debit_account_id, $credit_account_id, 300);

        $response = (new ListTransfers($this->getTransferRepository()))
            ->executeFromDebitAccount($debit_account_id, 2, 3);

        self::assertEquals(
            [
                new TransferDto(
                    $transfer_id_2,
                    $debit_account_id,
                    2,
                    $credit_account_id,
                    2,
                    1,
                    200,
                    (object)[],
                    $this->getNow(),
                ),
                new TransferDto(
                    $transfer_id_1,
                    $debit_account_id,
                    1,
                    $credit_account_id,
                    1,
                    1,
                    100,
                    (object)[],
                    $this->getNow(),
                ),
            ],
            $response
        );
    }

    public function testListTransfersFromDebitAccount_LimitGreaterThan100_ShouldThrowException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');
        (new ListTransfers($this->getTransferRepository()))
            ->executeFromDebitAccount(Uuid::uuid4(), 101);
    }

    public function testListTransfersFromDebitAccount_LimitLessThan1_ShouldThrowException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');
        (new ListTransfers($this->getTransferRepository()))
            ->executeFromDebitAccount(Uuid::uuid4(), 0);
    }
}
