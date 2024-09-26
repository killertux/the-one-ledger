<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\AccountDto;
use App\Infra\Repository\Account\AccountRepository;
use Ramsey\Uuid\UuidInterface;

class ListAccount {

    public function __construct(
        private AccountRepository $account_repository,
    ) {}

    public function execute(UuidInterface $account_id, int $limit, ?int $before_sequence = null): array {
        if ($limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
        return array_map(
            fn($account) => AccountDto::fromAccount($account),
            $this->account_repository->listAccount($account_id, $limit, $before_sequence)
        );
    }

}
