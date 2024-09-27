<?php declare(strict_types=1);

namespace App\Infra\Repository\Transfer;

use App\Domain\Entity\Money;
use App\Domain\Entity\Transfer;
use App\Domain\Repository\TransferNotFound;
use App\Domain\Repository\TransferRepository;
use Cake\Chronos\Chronos;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class CrdbTransferRepository implements TransferRepository {

    /** @param Transfer[] $transfers */
    public function createTransfers(array $transfers): void {
        DB::table('transfers')->insert(array_map(function($transfer) {
            return [
                'id' => $transfer->getId(),
                'debit_account_id' => $transfer->getDebitAccountId(),
                'debit_version' => $transfer->getDebitAccountVersion(),
                'credit_account_id' => $transfer->getCreditAccountId(),
                'credit_version' => $transfer->getCreditAccountVersion(),
                'currency' => $transfer->getAmount()->getCurrency(),
                'amount' => $transfer->getAmount()->getAmount(),
                'metadata' => json_encode($transfer->getMetadata()),
                'created_at' => $transfer->getCreatedAt(),
            ];
        }, $transfers));
    }

    public function getTransfer(UuidInterface $transfer_id): Transfer {
        $rows = DB::select(
            'SELECT * FROM transfers WHERE id = ?',
            [$transfer_id]
        );
        if (count($rows) === 0) {
            throw new TransferNotFound("Transfer not found: $transfer_id");
        }
        assert(count($rows) === 1, "ID is unique. So we can only have 1 row");

        return $this->getTransferFromRow($rows[0]);
    }

    public function listTransfersFromCreditAccount(UuidInterface $account_id, int $limit, ?int $before_version): array {
        $sql = 'SELECT * FROM transfers WHERE credit_account_id = ?';
        $params = [$account_id];
        if ($before_version !== null) {
            $sql .= ' AND credit_version < ?';
            $params[] = $before_version;
        }
        $sql .= ' ORDER BY credit_version DESC LIMIT ?';
        $params[] = $limit;

        $rows = DB::select($sql, $params);
        return array_map(fn(\stdClass $row) => $this->getTransferFromRow($row), $rows);
    }

    public function listTransfersFromDebitAccount(UuidInterface $account_id, int $limit, ?int $before_version): array {
        $sql = 'SELECT * FROM transfers WHERE debit_account_id = ?';
        $params = [$account_id];
        if ($before_version !== null) {
            $sql .= ' AND debit_version < ?';
            $params[] = $before_version;
        }
        $sql .= ' ORDER BY debit_version DESC LIMIT ?';
        $params[] = $limit;

        $rows = DB::select($sql, $params);
        return array_map(fn(\stdClass $row) => $this->getTransferFromRow($row), $rows);
    }

    public function getTransferFromCreditAccountAndVersion(UuidInterface $account_id, int $version): Transfer {
        $rows = DB::select(
            'SELECT * FROM transfers WHERE credit_account_id = ? AND credit_version = ?',
            [$account_id, $version]
        );
        if (count($rows) === 0) {
            throw new TransferNotFound("Transfer not found for credit account and version: $account_id, $version");
        }

        assert(count($rows) === 1, "ID is unique. So we can only have 1 row");

        return $this->getTransferFromRow($rows[0]);
    }

    public function getTransferFromDebitAccountAndVersion(UuidInterface $account_id, int $version): Transfer {
        $rows = DB::select(
            'SELECT * FROM transfers WHERE debit_account_id = ? AND debit_version = ?',
            [$account_id, $version]
        );
        if (count($rows) === 0) {
            throw new TransferNotFound("Transfer not found for debit account and version: $account_id, $version");
        }

        assert(count($rows) === 1, "ID is unique. So we can only have 1 row");

        return $this->getTransferFromRow($rows[0]);
    }

    private function getTransferFromRow(\stdClass $row): Transfer {
        return new Transfer(
            Uuid::fromString($row->id),
            Uuid::fromString($row->debit_account_id),
            $row->debit_version,
            Uuid::fromString($row->credit_account_id),
            $row->credit_version,
            new Money($row->amount, $row->currency),
            \json_decode($row->metadata),
            Chronos::parse($row->created_at)
        );
    }
}
