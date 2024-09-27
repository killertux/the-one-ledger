<?php declare(strict_types=1);

namespace App\Domain\Conditional;

use App\Domain\Account;

interface Conditional {

    public function check(Account $debit_account, Account $credit_account): bool;
    public function failMessage(): string;
}
