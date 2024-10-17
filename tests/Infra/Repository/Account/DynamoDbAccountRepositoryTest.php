<?php declare(strict_types=1);

namespace Tests\Infra\Repository\Account;

use App\Application\UseCase\OptimisticLockError;
use App\Domain\Entity\Account;
use App\Infra\Repository\Account\DynamoDbAccountRepository;
use App\Infra\Repository\DynamoDbTransaction;
use Aws\DynamoDb\DynamoDbClient;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class DynamoDbAccountRepositoryTest extends TestCase {

    public function testCreateAccountsMovementsTwice(): void {
        $dynamo_db_client = app(DynamoDbClient::class);
        $transaction = new DynamoDbTransaction($dynamo_db_client);
        $repository = new DynamoDbAccountRepository($dynamo_db_client);

        $id = Uuid::uuid4();
        $transaction->begin();
        $repository->createAccountMovements(
            $transaction,
            [
                new Account($id, 1, 1, 100, 100, $this->getNow()),
            ]
        );
        $transaction->commit();
        $transaction->begin();
        $repository->createAccountMovements(
            $transaction,
            [
                new Account($id, 1, 1, 100, 100, $this->getNow()),
            ]
        );
        $this->expectException(OptimisticLockError::class);
        $this->expectExceptionMessage('Optimistic lock error. Try again later');
        $transaction->commit();
    }
}
