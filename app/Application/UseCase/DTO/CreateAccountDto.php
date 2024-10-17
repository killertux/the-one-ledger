<?php declare(strict_types=1);

namespace App\Application\UseCase\DTO;

use Ramsey\Uuid\UuidInterface;

readonly class CreateAccountDto {

    public function __construct(
        public UuidInterface $account_id,
        public int $ledger_type,
    )
    {}
}
