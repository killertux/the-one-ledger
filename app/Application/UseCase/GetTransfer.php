<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\TransferDto;
use App\Infra\Repository\Transfer\TransferRepository;
use Ramsey\Uuid\UuidInterface;

readonly class GetTransfer {

    public function __construct(private TransferRepository $transfer_repository) {}

    public function execute(UuidInterface $transfer_id): TransferDto {
        return TransferDto::fromTransfer($this->transfer_repository->getTransfer($transfer_id));
    }
}
