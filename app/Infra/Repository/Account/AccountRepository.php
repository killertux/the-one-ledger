<?php declare(strict_types=1);

namespace App\Infra\Repository\Account;

use App\Domain\Account;
use Ramsey\Uuid\UuidInterface;

interface AccountRepository {

    public function getAccount(UuidInterface $id): Account;

    public function getAccountWithSequence(UuidInterface $id, int $sequence): Account;

    public function listAccount(UuidInterface $id, int $limit, ?int $before_sequence = null): array;

    /** @param Account[] $accounts */
    public function createAccountMovements(array $accounts): void;

    public function createAccount(UuidInterface $account_id, int $currency): Account;
}
