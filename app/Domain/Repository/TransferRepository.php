<?php declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Transfer;
use Ramsey\Uuid\UuidInterface;

interface TransferRepository {

    public function createTransfers(Transaction $transaction, array $transfers): void;

    public function getTransfer(UuidInterface $transfer_id): Transfer;

    public function listTransfersFromCreditAccount(UuidInterface $account_id, int $limit, ?int $before_version): array;

    public function listTransfersFromDebitAccount(UuidInterface $account_id, int $limit, ?int $before_version): array;

    public function getTransferFromCreditAccountAndVersion(UuidInterface $account_id, int $version): Transfer;

    public function getTransferFromDebitAccountAndVersion(UuidInterface $account_id, int $version): Transfer;
}
