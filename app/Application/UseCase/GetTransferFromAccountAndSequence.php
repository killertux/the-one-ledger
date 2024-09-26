<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Infra\Repository\Transfer\TransferRepository;
use Ramsey\Uuid\UuidInterface;

readonly class GetTransferFromAccountAndSequence {

    public function __construct(private TransferRepository $transfer_repository) {}

    public function executeForCreditAccount(UuidInterface $account_id, int $sequence): TransferDto {
        return TransferDto::fromTransfer(
            $this->transfer_repository->getTransferFromCreditAccountAndSequence($account_id, $sequence)
        );
    }

    public function executeForDebitAccount(UuidInterface $account_id, int $sequence): TransferDto {
        return TransferDto::fromTransfer(
            $this->transfer_repository->getTransferFromDebitAccountAndSequence($account_id, $sequence)
        );
    }
}
