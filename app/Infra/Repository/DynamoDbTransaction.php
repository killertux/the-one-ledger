<?php declare(strict_types=1);

namespace App\Infra\Repository;

use App\Application\UseCase\DuplicatedTransfer;
use App\Application\UseCase\OptimisticLockError;
use App\Domain\Repository\Transaction;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;

class DynamoDbTransaction implements Transaction {

    private array $items = [];

    public function __construct(private DynamoDbClient $dynamo_db_client) {
    }

    public function begin(): void {
        $this->items = [];
    }

    public function appendItems(array $items): void {
        $this->items = array_merge($this->items, $items);
    }

    public function commit(): void {
        try {
            $this->dynamo_db_client->transactWriteItems([
                'TransactItems' => $this->items
            ]);
        } catch (DynamoDbException $exception) {
            if (\str_contains($exception->getAwsErrorMessage(), 'ConditionalCheckFailed')) {
                $errors = rtrim(explode('[', $exception->getAwsErrorMessage())[1], ']');
                $errors = explode(',', $errors);
                foreach ($errors as $i => $error) {
                    $error = trim($error);
                    if ($error == 'ConditionalCheckFailed' && $this->items[$i]['Put']['TableName'] == 'accounts') {
                        throw new OptimisticLockError('Optimistic lock error. Try again later', previous: $exception);
                    }
                }
                throw new DuplicatedTransfer("One of the transfers is duplicated");
            }
            throw $exception;
        } finally {
            $this->items = [];
        }
    }

    public function rollback(): void {
        $this->items = [];
    }
}
