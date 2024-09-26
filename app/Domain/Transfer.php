<?php declare(strict_types=1);

namespace App\Domain;

use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

readonly class Transfer {

    public function __construct(
        private UuidInterface $id,
        private UuidInterface $debit_account_id,
        private int           $debit_version,
        private UuidInterface $credit_account_id,
        private int           $credit_version,
        private Money         $amount,
        private \stdClass     $metadata,
        private ?Chronos      $created_at = null
    ) {}

    public function getId(): UuidInterface {
        return $this->id;
    }

    public function getDebitAccountId(): UuidInterface {
        return $this->debit_account_id;
    }

    public function getDebitAccountVersion(): int {
        return $this->debit_version;
    }

    public function getCreditAccountId(): UuidInterface {
        return $this->credit_account_id;
    }

    public function getCreditAccountVersion(): int {
        return $this->credit_version;
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
