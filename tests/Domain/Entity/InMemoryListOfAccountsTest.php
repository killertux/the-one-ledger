<?php declare(strict_types=1);

namespace Tests\Domain\Entity;

use App\Domain\Entity\Account;
use App\Domain\Entity\InMemoryListOfAccounts;
use App\Domain\Repository\AccountNotFound;
use App\Domain\Repository\AccountRepository;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class InMemoryListOfAccountsTest extends TestCase {

    public function testGetAccount_IfNotInMemoryShouldLoadFromRepository(): void {
        $repository = $this->createMock(AccountRepository::class);
        $account = $this->createAccount();
        $repository
            ->expects($this->once())
            ->method('getAccount')
            ->with($account->getId())
            ->willReturn($account);
        $accounts = new InMemoryListOfAccounts($repository);

        self::assertSame($account, $accounts[$account->getId()]);
    }

    public function testGetAccountAfterASet_ShouldNotUseRepository(): void {
        $repository = $this->createMock(AccountRepository::class);
        $repository->expects($this->never())->method('getAccount');
        $account = $this->createAccount();

        $accounts = new InMemoryListOfAccounts($repository);
        $accounts[$account->getId()] = $account;
        self::assertSame($account, $accounts[$account->getId()]);
    }

    public function testGetAccountAccountNotFound_ShouldReturnError(): void {
        $repository = $this->createMock(AccountRepository::class);
        $not_found_account_id = Uuid::uuid4();
        $repository->method('getAccount')->willThrowException(new AccountNotFound($not_found_account_id));
        $accounts = new InMemoryListOfAccounts($repository);

        $this->expectException(AccountNotFound::class);
        $this->expectExceptionMessage("Account not found: $not_found_account_id");
        $accounts[$not_found_account_id];
    }

    private function createAccount(): Account {
        return new Account(Uuid::uuid4(), 1, 1, 100, 100);
    }
}
