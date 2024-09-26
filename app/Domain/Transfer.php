<?php declare(strict_types=1);

namespace App\Domain;

use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

class Transfer {

    public function __construct(
        private UuidInterface $id,
        private UuidInterface $debit_account_id,
        private int $debit_sequence,
        private UuidInterface $credit_account_id,
        private int $credit_sequence,
        private Money $amount,
        private \stdClass $metadata,
        private ?Chronos $created_at = null
    ) {}

    public function getId(): UuidInterface {
        return $this->id;
    }

    public function getDebitAccountId(): UuidInterface {
        return $this->debit_account_id;
    }

    public function getDebitAccountSequence(): int {
        return $this->debit_sequence;
    }

    public function getCreditAccountId(): UuidInterface {
        return $this->credit_account_id;
    }

    public function getCreditAccountSequence(): int {
        return $this->credit_sequence;
    }

    public function getAmount(): Money {
        return $this->amount;
    }

    public function getMetadata(): \stdClass {
        return $this->metadata;
    }

    public function getCreatedAt(): ?Chronos {
        return $this->created_at;
    }

}
