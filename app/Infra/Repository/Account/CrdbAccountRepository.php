<?php declare(strict_types=1);

namespace App\Infra\Repository\Account;

use App\Domain\Entity\Account;
use App\Domain\Repository\AccountAlreadyExists;
use App\Domain\Repository\AccountNotFound;
use App\Domain\Repository\AccountRepository;
use Cake\Chronos\Chronos;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

readonly class CrdbAccountRepository implements AccountRepository {

    public function getAccount(UuidInterface $id): Account {
        $rows = DB::select(
            'SELECT * FROM accounts WHERE id = ? ORDER BY PRIMARY KEY accounts LIMIT 1',
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
                    'version' => $account->getVersion(),
                    'ledger_type' => $account->getLedgerType(),
                    'debit_amount' => $account->getDebitAmount(),
                    'credit_amount' => $account->getCreditAmount(),
                    'datetime' => $account->getDatetime()
                ],
                $accounts
            )
        );
    }

    public function createAccount(UuidInterface $account_id, int $ledger_type): Account {
        try {
            DB::statement(
                'INSERT INTO accounts (id, version, ledger_type, debit_amount, credit_amount, datetime) VALUES (?, 0, ?, 0, 0, ?)',
                [$account_id, $ledger_type, Chronos::now()]
            );
        } catch (UniqueConstraintViolationException $_) {
            throw new AccountAlreadyExists($account_id);
        }
        return $this->getAccount($account_id);
    }

    public function getAccountWithVersion(UuidInterface $id, int $version): Account {
        $rows = DB::select(
            'SELECT * FROM accounts WHERE id = ? AND version = ?',
            [$id, $version]
        );
        if (count($rows) === 0) {
            throw new AccountNotFound($id);
        }
        assert(count($rows) === 1, "Query with a LIMIT 1. This should always be 1");

        return $this->getAccountFromRow($rows[0]);
    }

    public function listAccount(UuidInterface $id, int $limit, ?int $before_version = null): array {
        $query = 'SELECT * FROM accounts WHERE id = ?';
        $params = [$id];
        if ($before_version !== null) {
            $query .= ' AND version < ?';
            $params[] = $before_version;
        }
        $params[] = $limit;
        $query .= ' ORDER BY PRIMARY KEY accounts LIMIT ?';
        $rows = DB::select($query, $params);
        return array_map(fn($row) => $this->getAccountFromRow($row), $rows);
    }

    private function getAccountFromRow(\stdClass $row): Account {
        return new Account(
            Uuid::fromString($row->id),
            $row->version,
            $row->ledger_type,
            $row->debit_amount,
            $row->credit_amount,
            Chronos::parse($row->datetime)
        );
    }
}
