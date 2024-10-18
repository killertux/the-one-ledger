<?php declare(strict_types=1);

namespace App\Application\UseCase;

use App\Application\UseCase\DTO\AccountDto;
use App\Application\UseCase\DTO\CreateTransferDtoCollection;
use App\Application\UseCase\DTO\ExecuteTransfersResponseDto;
use App\Application\UseCase\DTO\TransferDto;
use App\Domain\Entity\Account;
use App\Domain\Entity\Conditional\Conditional;
use App\Domain\Entity\InMemoryListOfAccounts;
use App\Domain\Entity\Transfer;
use App\Domain\Repository\AccountRepository;
use App\Domain\Repository\Transaction;
use App\Domain\Repository\TransferRepository;
use App\Infra\Utils\Sleeper;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\UuidInterface;

readonly class ExecuteTransfers {

    public function __construct(
        private AccountRepository $account_repository,
        private TransferRepository $transfer_repository,
        private Transaction $transaction,
        private Sleeper $sleeper,
    ) {}

    public function execute(CreateTransferDtoCollection $transfer_dto_collection) {
        $tries = 0;
        while ($tries < 5) {
            try {
                return $this->internalExecute($transfer_dto_collection);
            } catch (OptimisticLockError $error) {
                Log::warning($error->getMessage());
                $this->sleeper->sleep(random_int(10, 300));
                $tries++;
            }
        }
        throw $error;
    }

    private function internalExecute(CreateTransferDtoCollection $transfer_dto_collection): ExecuteTransfersResponseDto {
        if (count($transfer_dto_collection) > 30) {
            throw new \InvalidArgumentException('Too many transfers. Max 30 per request');
        }

        $list_of_accounts_by_id = new InMemoryListOfAccounts($this->account_repository);
        $list_of_accounts_to_create = [];
        $list_of_transfers_to_create = [];

        foreach ($transfer_dto_collection as $transfer_dto) {
            if ($transfer_dto->debit_account_id->equals($transfer_dto->credit_account_id)) {
                throw new SameAccountTransfer("Debit and credit account are the same. $transfer_dto->debit_account_id");
            }
            $debit_account = $list_of_accounts_by_id[$transfer_dto->debit_account_id];
            $credit_account = $list_of_accounts_by_id[$transfer_dto->credit_account_id];
            $debit_account = $debit_account->debit($transfer_dto->ledger_type, $transfer_dto->amount);
            $credit_account = $credit_account->credit($transfer_dto->ledger_type, $transfer_dto->amount);
            $this->validateConditionals($debit_account, $credit_account, $transfer_dto->transfer_id, $transfer_dto->conditionals);
            $transfer = $transfer_dto->intoTransfer($debit_account->getVersion(), $credit_account->getVersion());
            $list_of_transfers_to_create[] = $transfer;
            $list_of_accounts_to_create[] = $debit_account;
            $list_of_accounts_to_create[] = $credit_account;
            $list_of_accounts_by_id[$credit_account->getId()] = $credit_account;
            $list_of_accounts_by_id[$debit_account->getId()] = $debit_account;
        }

        $this->transaction->begin();
        try {
            $this->account_repository->createAccountMovements($this->transaction, $list_of_accounts_to_create);
            $this->transfer_repository->createTransfers($this->transaction, $list_of_transfers_to_create);
            $this->transaction->commit();
        } catch (\Throwable $throwable) {
            $this->transaction->rollback();
            throw $throwable;
        }
        return new ExecuteTransfersResponseDto(
            \array_map(fn(Account $account) => AccountDto::fromAccount($account), $list_of_accounts_to_create),
            \array_map(fn(Transfer $transfer) => TransferDto::fromTransfer($transfer), $list_of_transfers_to_create),
        );
    }


    /**
     * @param Account $debit_account
     * @param Account $credit_account
     * @param Conditional[] $conditionals
     * @return void
     */
    private function validateConditionals(
        Account $debit_account,
        Account $credit_account,
        UuidInterface $transfer_id,
        array $conditionals
    ): void {
        foreach ($conditionals as $conditional) {
            if (!$conditional->check($debit_account, $credit_account)) {
                throw new ConditionalNotSatisfied("Failed executing transfer {$transfer_id}. " . $conditional->failMessage());
            }
        }
    }
}
