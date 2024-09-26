<?php declare(strict_types=1);

namespace App\Application\UseCase\DTO;

use App\Domain\Money;
use App\Domain\Transfer;
use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

class TransferDto implements \JsonSerializable {

    public function __construct(
        public UuidInterface $id,
        public UuidInterface $debit_account_id,
        public int           $debit_version,
        public UuidInterface $credit_account_id,
        public int           $credit_version,
        public Money         $amount,
        public \stdClass     $metadata,
        public ?Chronos      $created_at = null
    ) {}

    public static function fromTransfer(Transfer $transfer): self {
        return new self(
            $transfer->getId(),
            $transfer->getDebitAccountId(),
            $transfer->getDebitAccountVersion(),
            $transfer->getCreditAccountId(),
            $transfer->getCreditAccountVersion(),
            $transfer->getAmount(),
            $transfer->getMetadata(),
            $transfer->getCreatedAt()
        );
    }


    public function jsonSerialize(): mixed {
        return [
            'id' => $this->id->toString(),
            'debit_account_id' => $this->debit_account_id->toString(),
            'debit_version' => $this->debit_version,
            'credit_account_id' => $this->credit_account_id->toString(),
            'credit_version' => $this->credit_version,
            'currency' => $this->amount->getCurrency(),
            'amount' => $this->amount->getAmount(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at->toIso8601String()
        ];
    }
}
