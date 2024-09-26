<?php declare(strict_types=1);

namespace App\Application\UseCase\DTO;

use App\Domain\Money;
use App\Domain\Transfer;
use Cake\Chronos\Chronos;
use Ramsey\Uuid\UuidInterface;

readonly class CreateTransferDto {

    public function __construct(
        public UuidInterface $transfer_id,
        public UuidInterface $debit_account_id,
        public UuidInterface $credit_account_id,
        public Money $amount,
        public \stdClass $metadata,
    ) {
    }

    public function intoTrasfer(
        int $debit_sequence,
        int $credit_sequence
    ): Transfer {
        return new Transfer(
            $this->transfer_id,
            $this->debit_account_id,
            $debit_sequence,
            $this->credit_account_id,
            $credit_sequence,
            $this->amount,
            $this->metadata,
            Chronos::now(),
        );
    }
}
