<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Domain\Transfer;
use App\Infra\Repository\Transfer\TransferRepository;
use Ramsey\Uuid\UuidInterface;

class ListTransfers {

    public function __construct(
        private TransferRepository $transfer_repository,
    ) {}

    public function executeFromCreditAccount(UuidInterface $account_id, int $limit, ?int $beforeSequence =  null): array {
        $this->validateLimit($limit);
        return array_map(
            fn(Transfer $transfer) => TransferDto::fromTransfer($transfer),
            $this->transfer_repository->listTransfersFromCreditAccount($account_id, $limit, $beforeSequence)
        );
    }

    public function executeFromDebitAccount(UuidInterface $account_id, int $limit, ?int $beforeSequence =  null): array {
        $this->validateLimit($limit);
        return array_map(
            fn(Transfer $transfer) => TransferDto::fromTransfer($transfer),
            $this->transfer_repository->listTransfersFromDebitAccount($account_id, $limit, $beforeSequence)
        );
    }

    private function validateLimit(int $limit): void {
        if ($limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
    }
}
