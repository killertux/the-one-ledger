<?php declare(strict_types=1);

namespace App\Infra\Repository\Account;

use App\Domain\Entity\Account;
use App\Domain\Repository\AccountAlreadyExists;
use App\Domain\Repository\AccountNotFound;
use App\Domain\Repository\AccountRepository;
use App\Domain\Repository\Transaction;
use App\Infra\Repository\DynamoDbTransaction;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Cake\Chronos\Chronos;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DynamoDbAccountRepository implements AccountRepository {
    public function __construct(private readonly DynamoDbClient $dynamo_db_client) {}


    public function getAccount(UuidInterface $id): Account {
        $result = $this->dynamo_db_client->query([
            'TableName' => 'accounts',
            'KeyConditionExpression' => 'id = :id',
            'ScanIndexForward' => false,
            'ExpressionAttributeValues' => [
                ':id' => ['S' => $id->toString()],
            ],
            'Limit' => 1,
        ]);
        if ($result->get('Count') === 0) {
            throw new AccountNotFound($id);
        }
        $items = $result->get('Items');
        assert(count($items) === 1, "Query with a LIMIT 1. This should always be 1");
        return $this->getAccountFromData($items[0]);
    }

    public function getAccountWithVersion(UuidInterface $id, int $version): Account {
        $result = $this->dynamo_db_client->query([
            'TableName' => 'accounts',
            'KeyConditionExpression' => 'id = :id AND version = :version',
            'ExpressionAttributeValues' => [
                ':id' => ['S' => $id->toString()],
                ':version' => ['N' => (int)$version],
            ],
            'Limit' => 1,
        ]);
        if ($result->get('Count') === 0) {
            throw new AccountNotFound($id);
        }
        $items = $result->get('Items');
        assert(count($items) === 1, "Query with a LIMIT 1. This should always be 1");
        return $this->getAccountFromData($items[0]);
    }

    public function listAccount(UuidInterface $id, int $limit, ?int $before_version = null): array {
        $key_condition_expression = 'id = :id';
        $expression_attribute_values = [
            ':id' => ['S' => $id->toString()],
        ];
        if ($before_version !== null) {
            $key_condition_expression .= ' AND version < :before_version';
            $expression_attribute_values[':before_version'] = ['N' => (string)$before_version];
        }
        $result = $this->dynamo_db_client->query([
            'TableName' => 'accounts',
            'KeyConditionExpression' => $key_condition_expression,
            'ExpressionAttributeValues' => $expression_attribute_values,
            'ScanIndexForward' => false,
            'Limit' => $limit,
        ]);
        return array_map(fn($item) => $this->getAccountFromData($item), $result->get('Items'));
    }

    public function createAccountMovements(Transaction $transaction, array $accounts): void {
        if (!($transaction instanceof DynamoDbTransaction)) {
            throw new \InvalidArgumentException("Transaction must be an instance of DynamoDbTransaction");
        }
        $transaction->appendItems(array_map(function(Account $account) {
            return [
                'Put' => [
                    'TableName' => 'accounts',
                    'Item' => [
                        'id' => ['S' => $account->getId()->toString()],
                        'version' => ['N' => (string)$account->getVersion()],
                        'ledger_type' => ['N' => (string)$account->getLedgerType()],
                        'debit_amount' => ['N' => (string)$account->getDebitAmount()],
                        'credit_amount' => ['N' => (string)$account->getCreditAmount()],
                        'datetime' => ['S' => $account->getDatetime()->toIso8601String()],
                    ],
                    'ConditionExpression' => 'attribute_not_exists(version) AND attribute_not_exists(id)',
                ],
            ];
        }, $accounts));
    }

    public function createAccount(UuidInterface $account_id, int $ledger_type): Account {
        try {
            $this->dynamo_db_client->putItem([
                'TableName' => 'accounts',
                'ConditionExpression' => 'attribute_not_exists(version)',
                'Item' => [
                    'id' => ['S' => $account_id->toString()],
                    'version' => ['N' => '0'],
                    'ledger_type' => ['N' => (string)$ledger_type],
                    'debit_amount' => ['N' => '0'],
                    'credit_amount' => ['N' => '0'],
                    'datetime' => ['S' => Chronos::now()->toIso8601String()],
                ],
            ]);
        } catch (DynamoDbException $e) {
            if($e->getAwsErrorMessage() === 'The conditional request failed') {
                throw new AccountAlreadyExists($account_id);
            }
            throw $e;
        }
        return new Account($account_id, 0, $ledger_type, 0, 0, Chronos::now());
    }

    private function getAccountFromData(array $data): Account {
        return new Account(
            Uuid::fromString($data['id']['S']),
            (int)$data['version']['N'],
            (int)$data['ledger_type']['N'],
            (int)$data['debit_amount']['N'],
            (int)$data['credit_amount']['N'],
            Chronos::parse($data['datetime']['S']),
        );
    }
}
