<?php declare(strict_types=1);

namespace App\Infra\Controller;

use App\Application\UseCase\ConditionalNotSatisfied;
use App\Application\UseCase\DTO\CreateTransferDto;
use App\Application\UseCase\DTO\CreateTransferDtoCollection;
use App\Application\UseCase\DuplicatedTransfer;
use App\Application\UseCase\ExecuteTransfers;
use App\Application\UseCase\GetTransfer;
use App\Application\UseCase\GetTransferFromAccountAndVersion;
use App\Application\UseCase\ListTransfers;
use App\Application\UseCase\OptimisticLockError;
use App\Application\UseCase\SameAccountTransfer;
use App\Domain\Entity\Conditional\DebitAccountBalanceGreaterThanOrEqualTo;
use App\Domain\Entity\DifferentLedgerType;
use App\Domain\Repository\AccountNotFound;
use App\Domain\Repository\AccountRepository;
use App\Domain\Repository\TransferNotFound;
use App\Domain\Repository\TransferRepository;
use App\Infra\Utils\Sleeper;
use EBANX\Stream\Stream;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

readonly class TransferController {

    public function __construct(
        private AccountRepository $account_repository,
        private TransferRepository $transfer_repository,
        private Sleeper $sleeper,
    ) {}

    public function executeTransfers(Request $request): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($request) {
                $transfers = Stream::of($request->json()->all())
                    ->map(fn(array $transfer) => new CreateTransferDto(
                        Uuid::fromString($transfer['transfer_id']),
                        Uuid::fromString($transfer['debit_account_id']),
                        Uuid::fromString($transfer['credit_account_id']),
                        (int)$transfer['ledger_type'],
                        (int)$transfer['amount'],
                        (object) $transfer['metadata'],
                        self::buildConditionals($transfer['conditionals'] ?? []),
                    ))
                    ->collect();

                return (new ExecuteTransfers($this->account_repository, $this->transfer_repository, $this->sleeper))
                    ->execute(new CreateTransferDtoCollection($transfers));
            },
            201
        );
    }

    public function getTransfer(string $transfer_id): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($transfer_id) {
                $transfer_id = Uuid::fromString($transfer_id);
                return (new GetTransfer($this->transfer_repository))
                    ->execute($transfer_id);
            },
            200
        );
    }

    public function listTransferFromCreditAccount(Request $request, string $account_id): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($request, $account_id) {
                $limit = (int)$request->query('limit', 100);
                $before_version = $request->query('beforeVersion');
                $account_id = Uuid::fromString($account_id);
                return (new ListTransfers($this->transfer_repository))
                    ->executeFromCreditAccount($account_id, $limit, $before_version ? (int)$before_version : null);
            },
            200
        );
    }

    public function listTransferFromDebitAccount(Request $request, string $account_id): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($request, $account_id) {
                $limit = (int)$request->query('limit', 100);
                $before_version = $request->query('beforeVersion');
                $account_id = Uuid::fromString($account_id);
                return (new ListTransfers($this->transfer_repository))
                    ->executeFromDebitAccount($account_id, $limit, $before_version ? (int)$before_version : null);
            },
            200
        );
    }

    public function getTransferFromCreditAccountAndVersion(string $account_id, int $version): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($account_id, $version) {
                $account_id = Uuid::fromString($account_id);
                return (new GetTransferFromAccountAndVersion($this->transfer_repository))
                    ->executeForCreditAccount($account_id, $version);
            },
            200
        );
    }

    public function getTransferFromDebitAccountAndVersion(string $account_id, int $version): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($account_id, $version) {
                $account_id = Uuid::fromString($account_id);
                return (new GetTransferFromAccountAndVersion($this->transfer_repository))
                    ->executeForDebitAccount($account_id, $version);
            },
            200
        );
    }

    private static function buildConditionals(array $conditionals): array {
        return Stream::of($conditionals)
            ->map(function(array $conditional) {
                return match($conditional['type']) {
                    'debit_account_balance_greater_than_or_equal_to' => new DebitAccountBalanceGreaterThanOrEqualTo((int) $conditional['value']),
                };
            })
            ->collect();
    }

    private function executeCallableAndReturnJson(callable $callable, int $status_code): JsonResponse {
        try {
            $response = $callable();
        } catch (AccountNotFound $account_not_found) {
            return response()
                ->json(['error' => $account_not_found->getMessage()], 404);
        } catch (OptimisticLockError $optimistic_lock_error) {
            return response()
                ->json(['error' => $optimistic_lock_error->getMessage()], 409);
        } catch (DuplicatedTransfer $duplicated_transfer) {
            return response()
                ->json(['error' => $duplicated_transfer->getMessage()], 409);
        } catch (DifferentLedgerType $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 422);
        } catch (SameAccountTransfer $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 422);
        } catch (\InvalidArgumentException $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 400);
        } catch (TransferNotFound $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 404);
        } catch (ConditionalNotSatisfied $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 409);
        } catch (\Throwable $throwable) {
            return response()
                ->json(['error' => $throwable->getMessage()], 500);
        }
        return response()
            ->json($response, $status_code);
    }

}
