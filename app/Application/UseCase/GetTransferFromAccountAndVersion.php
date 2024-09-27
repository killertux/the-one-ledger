<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Domain\Repository\TransferRepository;
use Ramsey\Uuid\UuidInterface;

readonly class GetTransferFromAccountAndVersion {

    public function __construct(private TransferRepository $transfer_repository) {}

    public function executeForCreditAccount(UuidInterface $account_id, int $version): TransferDto {
        return TransferDto::fromTransfer(
            $this->transfer_repository->getTransferFromCreditAccountAndVersion($account_id, $version)
        );
    }

    public function executeForDebitAccount(UuidInterface $account_id, int $version): TransferDto {
        return TransferDto::fromTransfer(
            $this->transfer_repository->getTransferFromDebitAccountAndVersion($account_id, $version)
        );
    }
}
