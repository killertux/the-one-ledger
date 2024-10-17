<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private readonly \Aws\DynamoDb\DynamoDbClient $db_client;
    public function __construct() {
        $this->db_client = app(\Aws\DynamoDb\DynamoDbClient::class);
    }

    public function up(): void
    {
        $this->db_client->createTable([
            'TableName' => 'transfers',
            'KeySchema' => [
                [
                    'AttributeName' => 'id',
                    'KeyType' => 'HASH',
                ],
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'id',
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => 'credit_account_id',
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => 'debit_account_id',
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => 'credit_version',
                    'AttributeType' => 'N',
                ],
                [
                    'AttributeName' => 'debit_version',
                    'AttributeType' => 'N',
                ],
            ],
            'GlobalSecondaryIndexes' => [
                [
                    'IndexName' => 'credit_account_id',
                    'KeySchema' => [
                        [
                            'AttributeName' => 'credit_account_id',
                            'KeyType' => 'HASH',
                        ],
                        [
                            'AttributeName' => 'credit_version',
                            'KeyType' => 'RANGE',
                        ],
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL',
                    ],
                    'ProvisionedThroughput' => [
                        'ReadCapacityUnits' => 5,
                        'WriteCapacityUnits' => 5,
                    ],
                ],
                [
                    'IndexName' => 'debit_account_id',
                    'KeySchema' => [
                        [
                            'AttributeName' => 'debit_account_id',
                            'KeyType' => 'HASH',
                        ],
                        [
                            'AttributeName' => 'debit_version',
                            'KeyType' => 'RANGE',
                        ],
                    ],
                    'Projection' => [
                        'ProjectionType' => 'ALL',
                    ],
                    'ProvisionedThroughput' => [
                        'ReadCapacityUnits' => 5,
                        'WriteCapacityUnits' => 5,
                    ],
                ],
            ],
            'ProvisionedThroughput' => [
                'ReadCapacityUnits' => 5,
                'WriteCapacityUnits' => 5,
            ],
        ]);
    }

    public function down(): void
    {
        $this->db_client->deleteTable([
            'TableName' => 'transfers',
        ]);
    }
};
