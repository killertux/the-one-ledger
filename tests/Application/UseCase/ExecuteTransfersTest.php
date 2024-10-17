<?php declare(strict_types=1);

namespace Tests\Application\UseCase;

use App\Application\UseCase\ConditionalNotSatisfied;
use App\Application\UseCase\DTO\AccountDto;
use App\Application\UseCase\DTO\CreateTransferDto;
use App\Application\UseCase\DTO\CreateTransferDtoCollection;
use App\Application\UseCase\DTO\ExecuteTransfersResponseDto;
use App\Application\UseCase\DTO\TransferDto;
use App\Application\UseCase\DuplicatedTransfer;
use App\Application\UseCase\ExecuteTransfers;
use App\Application\UseCase\ListAccount;
use App\Application\UseCase\OptimisticLockError;
use App\Application\UseCase\SameAccountTransfer;
use App\Domain\Entity\Account;
use App\Domain\Entity\Conditional\DebitAccountBalanceGreaterThanOrEqualTo;
use App\Domain\Repository\AccountRepository;
use EBANX\Stream\Stream;
use Illuminate\Database\UniqueConstraintViolationException;
use Ramsey\Uuid\Uuid;
use Tests\Support\AccountUtils;
use Tests\TestCase;

class ExecuteTransfersTest extends TestCase {
    use AccountUtils;

    public function testExecuteTransfers(): void {
        $account_1 = $this->createAccount();
        $account_2 = $this->createAccount();

        $transfer_id_1 = Uuid::uuid4();
        $transfer_id_2 = Uuid::uuid4();

        $response = $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto($transfer_id_1, $account_1, $account_2, 1, 100, (object)['description' => 'some_description 1']),
                    new CreateTransferDto($transfer_id_2, $account_2, $account_1, 1, 150, (object)['description' => 'some_description 2']),
                ])
            );
        self::assertEquals(
            new ExecuteTransfersResponseDto(
                [
                    new AccountDto($account_1, 1, 1, 100, 0, $this->getNow()),
                    new AccountDto($account_2, 1, 1, 0, 100, $this->getNow()),
                    new AccountDto($account_2, 2, 1, 150, 100,$this->getNow()),
                    new AccountDto($account_1, 2, 1, 100, 150, $this->getNow()),
                ],
                [
                    new TransferDto($transfer_id_1, $account_1, 1, $account_2, 1, 1, 100, (object)['description' => 'some_description 1'], $this->getNow()),
                    new TransferDto($transfer_id_2, $account_2, 2, $account_1, 2, 1, 150, (object)['description' => 'some_description 2'], $this->getNow()),
                ]
            ),
            $response
        );
    }

    public function testProcessSameTransferTwice_ShouldThrowError(): void {
        $account_1 = $this->createAccount();
        $account_2 = $this->createAccount();

        $this->expectException(DuplicatedTransfer::class);
        $this->expectExceptionMessage('One of the transfers is duplicated');

        $transfer_id = Uuid::uuid4();

        $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto($transfer_id, $account_1, $account_2, 1, 100, (object)[]),
                ])
            );
        $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto($transfer_id, $account_1, $account_2, 1, 100, (object)[]),
                ])
            );
    }

    public function testTransferWithSameCreditAndDebitAccount_ShouldThrowError(): void {
        $account = $this->createAccount();
        $this->expectException(SameAccountTransfer::class);
        $this->expectExceptionMessage("Debit and credit account are the same. $account");
        $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto(Uuid::uuid4(), $account, $account, 1, 100, (object)[]),
                ])
            );
    }

    public function testUniqueConstraintErrorInCreateAccountMovements_ShouldConsiderAsOptimisticLockErrorAndRetry(): void {
        $account_1_id = $this->createAccount();
        $account_2_id = $this->createAccount();
        $account_repository = $this->createMock(AccountRepository::class);
        $account_repository->expects($this->exactly(5))
            ->method('createAccountMovements')
            ->with([
                new Account($account_1_id, 1, 1, 100, 0, $this->getNow()),
                new Account($account_2_id, 1, 1, 0, 100, $this->getNow()),
            ])
            ->willThrowException(new UniqueConstraintViolationException('', '', [], new \Exception()));
        $account_1 = new Account($account_1_id, 0, 1, 0, 0, $this->getNow());
        $account_2 = new Account($account_2_id, 0, 1, 0, 0, $this->getNow());
        $account_repository->method('getAccount')
            ->willReturnOnConsecutiveCalls(
                $account_1, $account_2, $account_1, $account_2, $account_1, $account_2, $account_1, $account_2, $account_1, $account_2,
            );

        $transfer_id = Uuid::uuid4();
        try {
            $this->createExecuteTransfers($account_repository)
                ->execute(
                    new CreateTransferDtoCollection([
                        new CreateTransferDto($transfer_id, $account_1_id, $account_2_id, 1, 100, (object)[]),
                    ])
                );
        } catch (OptimisticLockError $error) {
            self::assertEquals('Optimistic lock error. Try again later', $error->getMessage());
            return;
        }
        self::fail('Should return an exception');
    }

    public function testMultipleTransfersWithOneWithError_NoTransferShouldBeCommited(): void {
        $account_1 = $this->createAccount();
        $account_2 = $this->createAccount();

        $transfer_id_1 = Uuid::uuid4();
        $transfer_id_2 = Uuid::uuid4();

        $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto($transfer_id_2, $account_1, $account_2, 1, 100, (object)['description' => 'some_description 1']),
                ])
            );

        try {
            $this->createExecuteTransfers()
                ->execute(
                    new CreateTransferDtoCollection([
                        new CreateTransferDto($transfer_id_1, $account_1, $account_2, 1, 100, (object)['description' => 'some_description 1']),
                        new CreateTransferDto($transfer_id_2, $account_2, $account_1, 1, 150, (object)['description' => 'some_description 2']),
                    ])
                );
        } catch (DuplicatedTransfer $error) {
            self::assertEquals('One of the transfers is duplicated', $error->getMessage());
            self::assertCount(2, (new ListAccount($this->getAccountRepository()))->execute($account_1, 100));
            self::assertCount(2, (new ListAccount($this->getAccountRepository()))->execute($account_2, 100));
            return;
        }
        self::fail('Should return an exception');
    }

    public function testMoreThan30Transfers_ShouldReturnError(): void {
        $account_1 = $this->createAccount();
        $account_2 = $this->createAccount();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Too many transfers. Max 30 per request');

        $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection(
                    Stream::rangeInt(0, 31)
                        ->map(fn() => new CreateTransferDto(Uuid::uuid4(), $account_1, $account_2, 1, 100, (object)[]))
                        ->collect()
                   )
            );

    }

    public function testConditionalPassing(): void {
        $account_1 = $this->createAccount();
        $account_2 = $this->createAccount();

        $transfer_id = Uuid::uuid4();

        $response = $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto(
                        $transfer_id,
                        $account_1,
                        $account_2,
                        1,
                        100,
                        (object)['description' => 'some_description 1'],
                        [new DebitAccountBalanceGreaterThanOrEqualTo(-100)]
                    ),
                ])
            );

        self::assertEquals(
            new ExecuteTransfersResponseDto(
                [
                    new AccountDto($account_1, 1, 1, 100, 0, $this->getNow()),
                    new AccountDto($account_2, 1, 1, 0, 100, $this->getNow()),
                ],
                [
                    new TransferDto($transfer_id, $account_1, 1, $account_2, 1, 1, 100, (object)['description' => 'some_description 1'], $this->getNow()),
                ]
            ),
            $response
        );
    }

    public function testConditionalNotPassing(): void {
        $account_1 = $this->createAccount();
        $account_2 = $this->createAccount();

        $transfer_id = Uuid::uuid4();

        $this->expectException(ConditionalNotSatisfied::class);
        $this->expectExceptionMessage("Failed executing transfer {$transfer_id}. Debit account balance would be less than 0");

        $this->createExecuteTransfers()
            ->execute(
                new CreateTransferDtoCollection([
                    new CreateTransferDto(
                        $transfer_id,
                        $account_1,
                        $account_2,
                        1,
                        100,
                        (object)['description' => 'some_description 1'],
                        [new DebitAccountBalanceGreaterThanOrEqualTo(0)]
                    ),
                ])
            );
    }

    private function createExecuteTransfers(AccountRepository $account_repository = null): ExecuteTransfers {
        return new ExecuteTransfers(
            $account_repository ?? $this->getAccountRepository(),
            $this->getTransferRepository(),
            $this->getSleeper()
        );
    }

}
