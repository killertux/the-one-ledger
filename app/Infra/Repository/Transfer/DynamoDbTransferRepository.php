<?php declare(strict_types=1);

namespace App\Infra\Repository\Transfer;

use App\Application\UseCase\DuplicatedTransfer;
use App\Domain\Entity\Transfer;
use App\Domain\Repository\Transaction;
use App\Domain\Repository\TransferNotFound;
use App\Domain\Repository\TransferRepository;
use App\Infra\Repository\DynamoDbTransaction;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\DynamoDbException;
use Cake\Chronos\Chronos;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class DynamoDbTransferRepository implements TransferRepository {

    public function __construct(private readonly DynamoDbClient $dynamo_db_client) {}
    public function createTransfers(Transaction $transaction, array $transfers): void {
        if (!($transaction instanceof DynamoDbTransaction)) {
            throw new \InvalidArgumentException("Transaction must be an instance of DynamoDbTransaction");
        }
        $transaction->appendItems(array_map(function (Transfer $transfer) {
            return [
                'Put' => [
                    'TableName' => 'transfers',
                    'Item' => [
                        'id' => ['S' => $transfer->getId()->toString()],
                        'debit_account_id' => ['S' => $transfer->getDebitAccountId()->toString()],
                        'debit_version' => ['N' => (string)$transfer->getDebitAccountVersion()],
                        'credit_account_id' => ['S' => $transfer->getCreditAccountId()->toString()],
                        'credit_version' => ['N' => (string)$transfer->getCreditAccountVersion()],
                        'ledger_type' => ['N' => (string)$transfer->getLedgerType()],
                        'amount' => ['N' => (string)$transfer->getAmount()],
                        'metadata' => ['S' => json_encode($transfer->getMetadata())],
                        'created_at' => ['S' => $transfer->getCreatedAt()->toIso8601String()],
                    ],
                    'ConditionExpression' => 'attribute_not_exists(id)',
                ]
            ];
        }, $transfers),);
    }

    public function getTransfer(UuidInterface $transfer_id): Transfer {
        $result = $this->dynamo_db_client->getItem([
            'TableName' => 'transfers',
            'Key' => [
                'id' => ['S' => $transfer_id->toString()],
            ],
        ]);
        if (empty($result->get('Item'))) {
            throw new TransferNotFound("Transfer not found: $transfer_id");
        }
        return $this->getTransferFromItem($result->get('Item'));
    }

    public function listTransfersFromCreditAccount(UuidInterface $account_id, int $limit, ?int $before_version): array {
        $key_condition_expression = 'credit_account_id = :credit_account_id';
        $expression_attribute_values = [
            ':credit_account_id' => ['S' => $account_id->toString()],
        ];
        if ($before_version !== null) {
            $key_condition_expression .= ' AND credit_version < :before_version';
            $expression_attribute_values[':before_version'] = ['N' => $before_version];
        }
        $response = $this->dynamo_db_client->query([
            'TableName' => 'transfers',
            'IndexName' => 'credit_account_id',
            'KeyConditionExpression' => $key_condition_expression,
            'ExpressionAttributeValues' => $expression_attribute_values,
            'Limit' => $limit,
            'ScanIndexForward' => false,
        ]);
        return array_map(fn(array $item) => $this->getTransferFromItem($item), $response->get('Items'));
    }

    public function listTransfersFromDebitAccount(UuidInterface $account_id, int $limit, ?int $before_version): array {
        $key_condition_expression = 'debit_account_id = :debit_account_id';
        $expression_attribute_values = [
            ':debit_account_id' => ['S' => $account_id->toString()],
        ];
        if ($before_version !== null) {
            $key_condition_expression .= ' AND debit_version < :before_version';
            $expression_attribute_values[':before_version'] = ['N' => $before_version];
        }
        $response = $this->dynamo_db_client->query([
            'TableName' => 'transfers',
            'IndexName' => 'debit_account_id',
            'KeyConditionExpression' => $key_condition_expression,
            'ExpressionAttributeValues' => $expression_attribute_values,
            'Limit' => $limit,
            'ScanIndexForward' => false,
        ]);
        return array_map(fn(array $item) => $this->getTransferFromItem($item), $response->get('Items'));
    }

    public function getTransferFromCreditAccountAndVersion(UuidInterface $account_id, int $version): Transfer {
        $response = $this->dynamo_db_client->query([
            'TableName' => 'transfers',
            'IndexName' => 'credit_account_id',
            'KeyConditionExpression' => 'credit_account_id = :credit_account_id AND credit_version = :credit_version',
            'ExpressionAttributeValues' => [
                ':credit_account_id' => ['S' => $account_id->toString()],
                ':credit_version' => ['N' => $version],
            ],
            'Limit' => 1,
        ]);
        $items = $response->get('Items');
        if (empty($items)) {
            throw new TransferNotFound("Transfer not found for credit account and version: $account_id, $version");
        }
        assert(count($items) === 1, "Query with a LIMIT 1. This should always be 1");
        return $this->getTransferFromItem($items[0]);
    }

    public function getTransferFromDebitAccountAndVersion(UuidInterface $account_id, int $version): Transfer {
        $response = $this->dynamo_db_client->query([
            'TableName' => 'transfers',
            'IndexName' => 'debit_account_id',
            'KeyConditionExpression' => 'debit_account_id = :debit_account_id AND debit_version = :debit_version',
            'ExpressionAttributeValues' => [
                ':debit_account_id' => ['S' => $account_id->toString()],
                ':debit_version' => ['N' => $version],
            ],
            'Limit' => 1,
        ]);
        $items = $response->get('Items');
        if (empty($items)) {
            throw new TransferNotFound("Transfer not found for debit account and version: $account_id, $version");
        }
        assert(count($items) === 1, "Query with a LIMIT 1. This should always be 1");
        return $this->getTransferFromItem($items[0]);
    }

    private function getTransferFromItem(array $item): Transfer {
        return new Transfer(
            Uuid::fromString($item['id']['S'] ?? $item['transfer_id']['S']),
            Uuid::fromString($item['debit_account_id']['S']),
            (int)$item['debit_version']['N'],
            Uuid::fromString($item['credit_account_id']['S']),
            (int)$item['credit_version']['N'],
            (int)$item['ledger_type']['N'],
            (int)$item['amount']['N'],
            json_decode($item['metadata']['S']),
            Chronos::parse($item['created_at']['S'])
        );
    }
}
