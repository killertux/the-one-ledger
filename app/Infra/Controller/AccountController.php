<?php declare(strict_types=1);

namespace App\Infra\Controller;

use App\Application\UseCase\CreateAccount;
use App\Application\UseCase\DTO\CreateAccountDto;
use App\Application\UseCase\GetAccount;
use App\Application\UseCase\ListAccount;
use App\Infra\Repository\Account\AccountAlreadyExists;
use App\Infra\Repository\Account\AccountNotFound;
use App\Infra\Repository\Account\AccountRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

readonly class AccountController {

    public function __construct(
        private AccountRepository $account_repository,
    ) {}

    public function createAccount(Request $request): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($request) {
                $account_id = $request->string('account_id')->toString();
                $account_id = Uuid::fromString($account_id);
                $currency = (int) $request->input('currency');
                return (new CreateAccount($this->account_repository))
                    ->execute(new CreateAccountDto($account_id, $currency));
            }
        );
    }

    public function getAccountWithSequence(string $account_id, int $sequence): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($account_id, $sequence) {
                $account_id = Uuid::fromString($account_id);
                return (new GetAccount($this->account_repository))
                    ->execute($account_id, $sequence);
            }
        );
    }

    public function listAccount(Request $request, string $account_id): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($request, $account_id) {
                $before_sequence = $request->query('beforeSequence');
                $limit = $request->query('limit') ?? 100;
                $account_id = Uuid::fromString($account_id);
                return (new ListAccount($this->account_repository))
                    ->execute(
                        $account_id,
                        (int) $limit,
                        $before_sequence ? (int) $before_sequence : null
                    );
            }
        );
    }

    private function executeCallableAndReturnJson(callable $callable): JsonResponse {
        try {
            $response = $callable();
        } catch (AccountAlreadyExists $account_already_exists) {
            return response()
                ->json(['error' => $account_already_exists->getMessage()], 409);
        } catch (AccountNotFound $account_not_found) {
            return response()
                ->json(['error' => $account_not_found->getMessage()], 404);
        } catch (\InvalidArgumentException $exception) {
            return response()
                ->json(['error' => $exception->getMessage()], 400);
        } catch (\Throwable $throwable) {
            return response()
                ->json(['error' => $throwable->getMessage()], 500);
        }
        return response()
            ->json($response);
    }
}
