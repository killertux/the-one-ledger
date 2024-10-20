<?php declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Account;
use Ramsey\Uuid\UuidInterface;

interface AccountRepository {

    public function getAccount(UuidInterface $id): Account;

    public function getAccountWithVersion(UuidInterface $id, int $version): Account;

    public function listAccount(UuidInterface $id, int $limit, ?int $before_version = null): array;

    public function createAccountMovements(Transaction $transaction, array $accounts): void;

    public function createAccount(UuidInterface $account_id, int $ledger_type): Account;
}
