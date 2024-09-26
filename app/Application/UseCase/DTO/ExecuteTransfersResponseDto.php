<?php declare(strict_types=1);

namespace App\Application\UseCase\DTO;

class ExecuteTransfersResponseDto implements \JsonSerializable {

    public function __construct(
        public array $accounts,
        public array $transfers,
    ) {}

    public function jsonSerialize(): mixed {
        return [
            'accounts'  => $this->accounts,
            'transfers' => $this->transfers,
        ];
    }
}
