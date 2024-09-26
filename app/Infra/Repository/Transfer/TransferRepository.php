<?php declare(strict_types=1);

namespace App\Infra\Repository\Transfer;

use App\Domain\Transfer;
use Ramsey\Uuid\UuidInterface;

interface TransferRepository {

    /** @param Transfer[] $transfers */
    public function createTransfers(array $transfers): void;

    public function getTransfer(UuidInterface $transfer_id): Transfer;

    public function listTransfersFromCreditAccount(UuidInterface $account_id, int $limit, ?int $before_sequence): array;

    public function listTransfersFromDebitAccount(UuidInterface $account_id, int $limit, ?int $before_sequence): array;

    public function getTransferFromCreditAccountAndSequence(UuidInterface $account_id, int $sequence): Transfer;

    public function getTransferFromDebitAccountAndSequence(UuidInterface $account_id, int $sequence): Transfer;
}
