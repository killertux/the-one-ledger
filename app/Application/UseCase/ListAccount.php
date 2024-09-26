<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\AccountDto;
use App\Domain\Account;
use App\Infra\Repository\Account\AccountRepository;
use Ramsey\Uuid\UuidInterface;

readonly class ListAccount {

    public function __construct(
        private AccountRepository $account_repository,
    ) {}

    public function execute(UuidInterface $account_id, int $limit, ?int $before_sequence = null): array {
        $this->validateLimit($limit);
        return array_map(
            fn(Account $account) => AccountDto::fromAccount($account),
            $this->account_repository->listAccount($account_id, $limit, $before_sequence)
        );
    }

    private function validateLimit(int $limit): void {
        if ($limit > 100) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
        if ($limit < 1) {
            throw new \InvalidArgumentException('Limit must be between 1 and 100');
        }
    }

}
