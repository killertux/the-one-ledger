<?php declare(strict_types=1);

namespace App\Domain\Entity\Conditional;

use App\Domain\Entity\Account;

readonly class DebitAccountBalanceGreaterThanOrEqualTo implements Conditional {

    public function __construct(
        private int $limit
    ) {}

    public function check(Account $debit_account, Account $credit_account): bool {
        $balance = $debit_account->getCreditAmount() - $debit_account->getDebitAmount();
        return $balance >= $this->limit;
    }

    public function failMessage(): string {
        return "Debit account balance would be less than {$this->limit}";
    }
}
