<?php declare(strict_types=1);

namespace App\Infra\Repository\Account;

use App\Domain\Account;
use App\Domain\Money;
use Cake\Chronos\Chronos;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class CrdbAccountRepository implements AccountRepository {

    public function getAccount(UuidInterface $id): Account {
        $rows = DB::select(
            'SELECT * FROM accounts WHERE id = ? ORDER BY sequence DESC LIMIT 1',
            [$id]
        );
        if (count($rows) === 0) {
            throw new AccountNotFound($id);
        }
        assert(count($rows) === 1, "Query with a LIMIT 1. This should always be 1");

        return $this->getAccountFromRow($rows[0]);
    }

    public function createAccountMovements(array $accounts): void {
        DB::table('accounts')->insert(
            array_map(
                fn(Account $account) => [
                    'id' => $account->getId(),
                    'sequence' => $account->getSequence(),
                    'currency' => $account->getDebitAmount()->getCurrency(),
                    'debit_amount' => $account->getDebitAmount()->getAmount(),
                    'credit_amount' => $account->getCreditAmount()->getAmount(),
                    'datetime' => $account->getDatetime()
                ],
                $accounts
            )
        );
    }

    public function createAccount(UuidInterface $account_id, int $currency): Account {
        try {
            DB::statement(
                'INSERT INTO accounts (id, sequence, currency, debit_amount, credit_amount, datetime) VALUES (?, 0, ?, 0, 0, ?)',
                [$account_id, $currency, Chronos::now()]
            );
        } catch (UniqueConstraintViolationException $_) {
            throw new AccountAlreadyExists($account_id);
        }
        return $this->getAccount($account_id);
    }

    private function getAccountFromRow(\stdClass $row): Account {
        return new Account(
            Uuid::fromString($row->id),
            $row->sequence,
            new Money($row->debit_amount, $row->currency),
            new Money($row->credit_amount, $row->currency),
            Chronos::parse($row->datetime)
        );
    }

    public function getAccountWithSequence(UuidInterface $id, int $sequence): Account {
        $rows = DB::select(
            'SELECT * FROM accounts WHERE id = ? AND sequence = ?',
            [$id, $sequence]
        );
        if (count($rows) === 0) {
            throw new AccountNotFound($id);
        }
        assert(count($rows) === 1, "Query with a LIMIT 1. This should always be 1");

        return $this->getAccountFromRow($rows[0]);
    }

    public function listAccount(UuidInterface $id, int $limit, ?int $before_sequence = null): array {
        $query = 'SELECT * FROM accounts WHERE id = ?';
        $params = [$id];
        if ($before_sequence !== null) {
            $query .= ' AND sequence < ?';
            $params[] = $before_sequence;
        }
        $params[] = $limit;
        $query .= ' ORDER BY sequence DESC LIMIT ?';
        $rows = DB::select($query, $params);
        return array_map(fn($row) => $this->getAccountFromRow($row), $rows);
    }
}
