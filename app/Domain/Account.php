<?php declare(strict_types=1);

namespace App\Domain;

use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

readonly class Account {

    public function __construct(
        private UuidInterface $id,
        private int $sequence,
        private Money $debit_amount,
        private Money $credit_amount,
        private ?Chronos $datetime = null
    ) {}

    public function getId(): UuidInterface {
        return $this->id;
    }

    public function credit(Money $money): Account {
        return new self(
            $this->id,
            $this->sequence + 1,
            $this->debit_amount,
            $this->credit_amount->add($money),
            Chronos::now()
        );
    }

    public function debit(Money $money): Account {
        return new self(
            $this->id,
            $this->sequence + 1,
            $this->debit_amount->add($money),
            $this->credit_amount,
            Chronos::now()
        );
    }

    public function getSequence(): int {
        return $this->sequence;
    }

    public function getDebitAmount(): Money {
        return $this->debit_amount;
    }

    public function getCreditAmount(): Money {
        return $this->credit_amount;
    }

    public function getDatetime(): ?Chronos {
        return $this->datetime;
    }

}
