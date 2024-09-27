<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\AccountDto;
use App\Application\UseCase\DTO\CreateAccountDto;
use App\Domain\Repository\AccountRepository;

readonly class CreateAccount {

    public function __construct(
        private AccountRepository $account_repository,
    ) {}

    public function execute(CreateAccountDto $create_account_dto): AccountDto {
        return AccountDto::fromAccount(
            $this->account_repository->createAccount($create_account_dto->account_id, $create_account_dto->currency)
        );
    }

}
