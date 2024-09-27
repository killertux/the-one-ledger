<?php declare(strict_types=1);

namespace App\Domain\Entity\Conditional;

use App\Domain\Entity\Account;

interface Conditional {

    public function check(Account $debit_account, Account $credit_account): bool;
    public function failMessage(): string;
}
