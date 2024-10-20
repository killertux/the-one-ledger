<?php declare(strict_types=1);

namespace App\Infra\Controller;

use App\Application\UseCase\CreateAccount;
use App\Application\UseCase\DTO\CreateAccountDto;
use App\Application\UseCase\GetAccount;
use App\Application\UseCase\ListAccount;
use App\Domain\Repository\AccountAlreadyExists;
use App\Domain\Repository\AccountNotFound;
use App\Domain\Repository\AccountRepository;
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
                $ledger_type = (int) $request->input('ledger_type');
                return (new CreateAccount($this->account_repository))
                    ->execute(new CreateAccountDto($account_id, $ledger_type));
            }
        );
    }

    public function getAccountWithVersion(string $account_id, int $version): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($account_id, $version) {
                $account_id = Uuid::fromString($account_id);
                return (new GetAccount($this->account_repository))
                    ->execute($account_id, $version);
            }
        );
    }

    public function listAccount(Request $request, string $account_id): JsonResponse {
        return $this->executeCallableAndReturnJson(
            function () use ($request, $account_id) {
                $before_version = $request->query('beforeVersion');
                $limit = $request->query('limit') ?? 100;
                $account_id = Uuid::fromString($account_id);
                return (new ListAccount($this->account_repository))
                    ->execute(
                        $account_id,
                        (int) $limit,
                        $before_version ? (int) $before_version : null
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
