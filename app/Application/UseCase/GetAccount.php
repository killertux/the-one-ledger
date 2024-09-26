<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\AccountDto;
use App\Infra\Repository\Account\AccountRepository;
use Ramsey\Uuid\UuidInterface;

readonly class GetAccount {

    public function __construct(
        private AccountRepository $account_repository,
    ) {}

    public function execute(UuidInterface $account_id, int $sequence): AccountDto {
        return AccountDto::fromAccount(
            $this->account_repository->getAccountWithSequence($account_id, $sequence)
        );
    }

}
