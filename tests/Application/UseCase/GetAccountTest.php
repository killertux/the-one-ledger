<?php declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\UseCase\DTO\CreateTransferDto;
use App\Application\UseCase\DTO\CreateTransferDtoCollection;
use App\Application\UseCase\ExecuteTransfers;
use App\Application\UseCase\GetAccount;
use App\Domain\Money;
use App\Infra\Repository\Account\AccountNotFound;
use App\Infra\Repository\Account\AccountRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\Support\AccountUtils;
use Tests\TestCase;

class GetAccountTest extends TestCase {
    use AccountUtils;

    public function testGetAccount(): void {
        $account_repository = $this->getAccountRepository();

        $account_id = $this->createAccount();

        $get_account = (new GetAccount($account_repository))
            ->execute($account_id, 0);

        self::assertEquals($account_id, $get_account->id);
        self::assertSame(0, $get_account->version);
        self::assertEquals(new Money(0, 1), $get_account->debit_amount);
        self::assertEquals(new Money(0, 1), $get_account->credit_amount);
    }

    public function testGetAccountWithMultipleVersions(): void {
        $account_repository = $this->getAccountRepository();

        $account_id = $this->createAccount();
        $this->creditAmountToAccount($account_repository, $account_id, 100);

        $get_account = (new GetAccount($account_repository))
            ->execute($account_id, 1);

        self::assertEquals($account_id, $get_account->id);
        self::assertSame(1, $get_account->version);
        self::assertEquals(new Money(0, 1), $get_account->debit_amount);
        self::assertEquals(new Money(100, 1), $get_account->credit_amount);
    }

    public function testGetAccountForNonExistentAccount_ShouldThrowError(): void {
        $account_repository = $this->getAccountRepository();
        $account_id = Uuid::uuid4();

        $this->expectException(AccountNotFound::class);
        $this->expectExceptionMessage("Account not found: $account_id");

        (new GetAccount($account_repository))
            ->execute($account_id, 0);
    }

    public function testGetAccountForExistentAccountButNonExistentVersion_ShouldThrowError(): void {
        $account_repository = $this->getAccountRepository();

        $account_id = $this->createAccount();

        $this->expectException(AccountNotFound::class);
        $this->expectExceptionMessage("Account not found: $account_id");

        (new GetAccount($account_repository))
            ->execute($account_id, 1);
    }

    private function creditAmountToAccount(AccountRepository $account_repository, UuidInterface $account_id, int $amount): void {
       $debit_account_id = $this->createAccount();
        (new ExecuteTransfers($account_repository, $this->getTransferRepository(), $this->getSleeper()))
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto(
                        Uuid::uuid4(),
                        $debit_account_id,
                        $account_id,
                        new Money($amount, 1),
                        (object)[]
                    )
                ])
            );
    }


}
