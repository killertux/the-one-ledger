<?php declare(strict_types=1);

namespace App\Infra\Repository\Transfer;

use App\Domain\Money;
use App\Domain\Transfer;
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
                'debit_sequence' => $transfer->getDebitAccountSequence(),
                'credit_account_id' => $transfer->getCreditAccountId(),
                'credit_sequence' => $transfer->getCreditAccountSequence(),
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

    public function listTransfersFromCreditAccount(UuidInterface $account_id, int $limit, ?int $before_sequence): array {
        $sql = 'SELECT * FROM transfers WHERE credit_account_id = ?';
        $params = [$account_id];
        if ($before_sequence !== null) {
            $sql .= ' AND credit_sequence < ?';
            $params[] = $before_sequence;
        }
        $sql .= ' ORDER BY credit_sequence DESC LIMIT ?';
        $params[] = $limit;

        $rows = DB::select($sql, $params);
        return array_map(fn(\stdClass $row) => $this->getTransferFromRow($row), $rows);
    }

    public function listTransfersFromDebitAccount(UuidInterface $account_id, int $limit, ?int $before_sequence): array {
        $sql = 'SELECT * FROM transfers WHERE debit_account_id = ?';
        $params = [$account_id];
        if ($before_sequence !== null) {
            $sql .= ' AND debit_sequence < ?';
            $params[] = $before_sequence;
        }
        $sql .= ' ORDER BY debit_sequence DESC LIMIT ?';
        $params[] = $limit;

        $rows = DB::select($sql, $params);
        return array_map(fn(\stdClass $row) => $this->getTransferFromRow($row), $rows);
    }

    public function getTransferFromCreditAccountAndSequence(UuidInterface $account_id, int $sequence): Transfer {
        $rows = DB::select(
            'SELECT * FROM transfers WHERE credit_account_id = ? AND credit_sequence = ?',
            [$account_id, $sequence]
        );
        if (count($rows) === 0) {
            throw new TransferNotFound("Transfer not found for credit account and sequence: $account_id, $sequence");
        }

        assert(count($rows) === 1, "ID is unique. So we can only have 1 row");

        return $this->getTransferFromRow($rows[0]);
    }

    public function getTransferFromDebitAccountAndSequence(UuidInterface $account_id, int $sequence): Transfer {
        $rows = DB::select(
            'SELECT * FROM transfers WHERE debit_account_id = ? AND debit_sequence = ?',
            [$account_id, $sequence]
        );
        if (count($rows) === 0) {
            throw new TransferNotFound("Transfer not found for debit account and sequence: $account_id, $sequence");
        }

        assert(count($rows) === 1, "ID is unique. So we can only have 1 row");

        return $this->getTransferFromRow($rows[0]);
    }

    private function getTransferFromRow(\stdClass $row): Transfer {
        return new Transfer(
            Uuid::fromString($row->id),
            Uuid::fromString($row->debit_account_id),
            $row->debit_sequence,
            Uuid::fromString($row->credit_account_id),
            $row->credit_sequence,
            new Money($row->amount, $row->currency),
            \json_decode($row->metadata),
            Chronos::parse($row->created_at)
        );
    }
}
