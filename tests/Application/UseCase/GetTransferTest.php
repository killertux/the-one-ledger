<?php declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Application\UseCase\GetTransfer;
use App\Domain\Money;
use App\Infra\Repository\Transfer\TransferNotFound;
use Ramsey\Uuid\Uuid;
use Tests\Support\AccountUtils;
use Tests\Support\TransferUtils;
use Tests\TestCase;

class GetTransferTest extends TestCase {
    use TransferUtils;
    use AccountUtils;

    public function testGetTransfer(): void {
        $credit_account_id = $this->createAccount();
        $debit_account_id = $this->createAccount();
        $transfer_id = $this->createTransfer($debit_account_id, $credit_account_id);

        $transfer = (new GetTransfer($this->getTransferRepository()))
            ->execute($transfer_id);

        self::assertEquals(
            new TransferDto(
                $transfer_id,
                $debit_account_id,
                1,
                $credit_account_id,
                1,
                new Money(100, 1),
                (object)[],
                $this->getNow(),
            ),
            $transfer
        );
    }

    public function testTransferNotFound(): void {
        $this->expectException(TransferNotFound::class);
        $this->expectExceptionMessage('Transfer not found');
        (new GetTransfer($this->getTransferRepository()))
            ->execute(Uuid::uuid4());

    }
}
