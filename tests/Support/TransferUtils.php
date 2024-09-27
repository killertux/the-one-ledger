<?php declare(strict_types=1);

namespace Tests\Support;

use App\Application\UseCase\DTO\CreateTransferDto;
use App\Application\UseCase\DTO\CreateTransferDtoCollection;
use App\Application\UseCase\ExecuteTransfers;
use App\Domain\Entity\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait TransferUtils {
    use AccountUtils;

    private function createTransfer(UuidInterface $debit_account_id = null, UuidInterface $credit_account_id = null, Money $amount = null): UuidInterface {
        $credit_account_id = $credit_account_id ?? $this->createAccount();
        $debit_account_id = $debit_account_id ?? $this->createAccount();
        $transfer_id = Uuid::uuid4();

        (new ExecuteTransfers($this->getAccountRepository(), $this->getTransferRepository(), $this->getSleeper()))
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto(
                        $transfer_id,
                        $debit_account_id,
                        $credit_account_id,
                        $amount ?? new Money(100, 1),
                        (object)[]
                    )
                ])
            );
        return $transfer_id;
    }
}
