<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Domain\Entity\Transfer;
use App\Domain\Repository\TransferRepository;
use Ramsey\Uuid\UuidInterface;

readonly class ListTransfers {

    public function __construct(
        private TransferRepository $transfer_repository,
    ) {}

    public function executeFromCreditAccount(UuidInterface $account_id, int $limit, ?int $before_version =  null): array {
        $this->validateLimit($limit);
        return array_map(
            fn(Transfer $transfer) => TransferDto::fromTransfer($transfer),
            $this->transfer_repository->listTransfersFromCreditAccount($account_id, $limit, $before_version)
        );
    }

    public function executeFromDebitAccount(UuidInterface $account_id, int $limit, ?int $before_version =  null): array {
        $this->validateLimit($limit);
        return array_map(
            fn(Transfer $transfer) => TransferDto::fromTransfer($transfer),
            $this->transfer_repository->listTransfersFromDebitAccount($account_id, $limit, $before_version)
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
