<?php declare(strict_types=1);

namespace App\Application\UseCase\DTO;

use App\Domain\Account;
use App\Domain\Money;
use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

class AccountDto implements \JsonSerializable {

    public function __construct(
        public UuidInterface $id,
        public int           $version,
        public Money         $debit_amount,
        public Money         $credit_amount,
        public Chronos       $datetime
    ) {}

    public static function fromAccount(Account $account): self {
        return new self(
            $account->getId(),
            $account->getVersion(),
            $account->getDebitAmount(),
            $account->getCreditAmount(),
            $account->getDatetime()
        );
    }


    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id->toString(),
            'version' => $this->version,
            'currency' => $this->debit_amount->getCurrency(),
            'debit_amount' => $this->debit_amount->getAmount(),
            'credit_amount' => $this->credit_amount->getAmount(),
            'balance' => $this->credit_amount->getAmount() - $this->debit_amount->getAmount(),
            'datetime' => $this->datetime->toIso8601String()
        ];
    }
}
