<?php declare(strict_types=1);

namespace App\Infra\Controller;

use App\Application\UseCase\DTO\CreateTransferDto;
use App\Application\UseCase\DTO\CreateTransferDtoCollection;
use App\Application\UseCase\DuplicatedTransfer;
use App\Application\UseCase\ExecuteTransfers;
use App\Application\UseCase\OptimisticLockError;
use App\Application\UseCase\SameAccountTransfer;
use App\Domain\DifferentCurrency;
use App\Domain\Money;
use App\Infra\Repository\Account\AccountNotFound;
use App\Infra\Repository\Account\AccountRepository;
use App\Infra\Repository\Transfer\TransferRepository;
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
                        new Money($transfer['amount'], $transfer['currency']),
                        (object) $transfer['metadata'],
                    ))
                    ->collect();

                return (new ExecuteTransfers($this->account_repository, $this->transfer_repository, $this->sleeper))
                    ->execute(new CreateTransferDtoCollection($transfers));
            },
            201
        );
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
        } catch (DifferentCurrency $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 422);
        } catch (SameAccountTransfer $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 422);
        } catch (\InvalidArgumentException $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 400);
        } catch (\Throwable $throwable) {
            return response()
                ->json(['error' => $throwable->getMessage()], 500);
        }
        return response()
            ->json($response, $status_code);
    }

}
