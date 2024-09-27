<?php declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\UseCase\DTO\AccountDto;
use App\Application\UseCase\ListAccount;
use App\Domain\Entity\Money;
use Ramsey\Uuid\Uuid;
use Tests\Support\AccountUtils;
use Tests\TestCase;

class ListAccountTest extends TestCase {
    use AccountUtils;

    public function testListAccountNonExistent(): void {
        $repository = $this->getAccountRepository();
        $response = (new ListAccount($repository))
            ->execute(Uuid::uuid4(), 100);

        self::assertSame([], $response);
    }

    public function testListAccountOnlyOneVersion(): void {
        $repository = $this->getAccountRepository();

        $account_id = $this->createAccount();

        $response = (new ListAccount($repository))
            ->execute($account_id, 100);

        self::assertEquals([
            new AccountDto(
                $account_id,
                0,
                new Money(0, 1),
                new Money(0, 1),
                $this->getNow(),
            )
        ], $response);
    }

    public function testListWithMultipleVersions_ShouldRespectLimit(): void {
        $repository = $this->getAccountRepository();

        $account_id = $this->createAccount();
        $this->creditAmountToAccount($account_id, 100);
        $this->creditAmountToAccount($account_id, 200);

        $response = (new ListAccount($repository))
            ->execute($account_id, 2);

        self::assertEquals([
            new AccountDto(
                $account_id,
                2,
                new Money(0, 1),
                new Money(300, 1),
                $this->getNow(),
            ),
            new AccountDto(
                $account_id,
                1,
                new Money(0, 1),
                new Money(100, 1),
                $this->getNow(),
            ),
        ], $response);
    }

    public function testListWithBeforeVersion(): void {
        $repository = $this->getAccountRepository();

        $account_id = $this->createAccount();
        $this->creditAmountToAccount($account_id, 100);
        $this->creditAmountToAccount($account_id, 200);

        $response = (new ListAccount($repository))
            ->execute($account_id, 2, 2);

        self::assertEquals([
            new AccountDto(
                $account_id,
                1,
                new Money(0, 1),
                new Money(100, 1),
                $this->getNow(),
            ),
            new AccountDto(
                $account_id,
                0,
                new Money(0, 1),
                new Money(0, 1),
                $this->getNow(),
            ),
        ], $response);
    }

    public function testLimitGreaterThan100_ShouldThrowException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');
        (new ListAccount($this->getAccountRepository()))
            ->execute(Uuid::uuid4(), 101);
    }

    public function testLimitLessThan1_ShouldThrowException(): void {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Limit must be between 1 and 100');
        (new ListAccount($this->getAccountRepository()))
            ->execute(Uuid::uuid4(), 0);
    }

}
