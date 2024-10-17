<?php declare(strict_types=1);

namespace App\Domain\Entity;

use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

readonly class Account {

    public function __construct(
        private UuidInterface $id,
        private int           $version,
        private int           $ledger_type,
        private int           $debit_amount,
        private int           $credit_amount,
        private ?Chronos      $datetime = null
    ) {}

    public function getId(): UuidInterface {
        return $this->id;
    }

    public function credit(int $ledger_type, int $amount): Account {
        $this->validateLedgerType($ledger_type);
        return new self(
            $this->id,
            $this->version + 1,
            $this->ledger_type,
            $this->debit_amount,
            $this->credit_amount + $amount,
            Chronos::now()
        );
    }

    public function debit(int $ledger_type, int $amount): Account {
        $this->validateLedgerType($ledger_type);
        return new self(
            $this->id,
            $this->version + 1,
            $this->ledger_type,
            $this->debit_amount + $amount,
            $this->credit_amount,
            Chronos::now()
        );
    }

    public function getVersion(): int {
        return $this->version;
    }

    public function getLedgerType(): int {
        return $this->ledger_type;
    }

    public function getDebitAmount(): int {
        return $this->debit_amount;
    }

    public function getCreditAmount(): int {
        return $this->credit_amount;
    }

    public function getDatetime(): ?Chronos {
        return $this->datetime;
    }

    private function validateLedgerType(int $ledger_type): void {
        if ($ledger_type !== $this->ledger_type) {
            throw new DifferentLedgerType($ledger_type, $this->ledger_type);
        }
    }

}
