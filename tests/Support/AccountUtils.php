<?php declare(strict_types=1);

namespace Tests\Support;

use App\Application\UseCase\CreateAccount;
use App\Application\UseCase\DTO\CreateAccountDto;
use App\Application\UseCase\DTO\CreateTransferDto;
use App\Application\UseCase\DTO\CreateTransferDtoCollection;
use App\Application\UseCase\ExecuteTransfers;
use App\Domain\Entity\Money;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

trait AccountUtils {

    private function createAccount(?UuidInterface $account_id = null, int $currency = 1): UuidInterface {
        $account_id = $account_id ?? Uuid::uuid4();
        (new CreateAccount($this->getAccountRepository()))
            ->execute(new CreateAccountDto($account_id, $currency));
        return $account_id;
    }

    private function creditAmountToAccount(UuidInterface $account_id, int $amount): void {
        $debit_account_id = $this->createAccount();
        (new ExecuteTransfers($this->getAccountRepository(), $this->getTransferRepository(), $this->getSleeper()))
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto(
                        Uuid::uuid4(),
                        $debit_account_id,
                        $account_id,
                        new Money($amount, 1),
                        (object)[]
                    )
                ])
            );
    }
}
