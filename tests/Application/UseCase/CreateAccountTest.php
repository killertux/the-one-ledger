<?php declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\UseCase\CreateAccount;
use App\Application\UseCase\DTO\CreateAccountDto;
use App\Domain\Entity\Money;
use App\Domain\Repository\AccountAlreadyExists;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class CreateAccountTest extends TestCase {

    public function testCreateAccount(): void {
        $account_repository = $this->getAccountRepository();
        $create_account = new CreateAccount($account_repository);
        $account_id = Uuid::uuid4();
        $account_dto = $create_account->execute(new CreateAccountDto($account_id, 1));

        self::assertEquals($account_id, $account_dto->id);
        self::assertSame(0, $account_dto->version);
        self::assertEquals(new Money(0, 1), $account_dto->debit_amount);
        self::assertEquals(new Money(0, 1), $account_dto->credit_amount);
        self::assertEquals($this->getNow(), $account_dto->datetime);
    }

    public function testCreateAnAlreadyExistentAccount_ShouldThrowError(): void {
        $account_repository = $this->getAccountRepository();
        $create_account = new CreateAccount($account_repository);
        $account_id = Uuid::uuid4();
        $create_account->execute(new CreateAccountDto($account_id, 1));

        $this->expectException(AccountAlreadyExists::class);
        $this->expectExceptionMessage("Account with id $account_id already exists");
        $create_account->execute(new CreateAccountDto($account_id, 1));
    }
}
