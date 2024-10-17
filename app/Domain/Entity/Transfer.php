<?php declare(strict_types=1);

namespace App\Domain\Entity;

use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

readonly class Transfer {

    public function __construct(
        private UuidInterface $id,
        private UuidInterface $debit_account_id,
        private int           $debit_version,
        private UuidInterface $credit_account_id,
        private int           $credit_version,
        private int           $ledger_type,
        private int           $amount,
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

    public function getLedgerType(): int {
        return $this->ledger_type;
    }

    public function getAmount(): int {
        return $this->amount;
    }

    public function getMetadata(): \stdClass {
        return $this->metadata;
    }

    public function getCreatedAt(): ?Chronos {
        return $this->created_at;
    }

}
