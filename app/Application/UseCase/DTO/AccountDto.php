<?php declare(strict_types=1);

namespace App\Application\UseCase\DTO;

use App\Domain\Entity\Account;
use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

class AccountDto implements \JsonSerializable {

    public function __construct(
        public UuidInterface $id,
        public int           $version,
        public int           $ledger_type,
        public int           $debit_amount,
        public int           $credit_amount,
        public Chronos       $datetime
    ) {}

    public static function fromAccount(Account $account): self {
        return new self(
            $account->getId(),
            $account->getVersion(),
            $account->getLedgerType(),
            $account->getDebitAmount(),
            $account->getCreditAmount(),
            $account->getDatetime()
        );
    }


    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id->toString(),
            'version' => $this->version,
            'ledger_type' => $this->ledger_type,
            'debit_amount' => $this->debit_amount,
            'credit_amount' => $this->credit_amount,
            'balance' => $this->credit_amount - $this->debit_amount,
            'datetime' => $this->datetime->toIso8601String()
        ];
    }
}
