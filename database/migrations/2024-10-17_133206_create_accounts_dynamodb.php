<?php declare(strict_types=1);

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    private readonly DynamoDbClient $db_client;
    public function __construct() {
        $this->db_client = app(DynamoDbClient::class);
    }

    public function up(): void
    {
        $this->db_client->createTable([
            'TableName' => 'accounts',
            'KeySchema' => [
                [
                    'AttributeName' => 'id',
                    'KeyType' => 'HASH',
                ],
                [
                    'AttributeName' => 'version',
                    'KeyType' => 'RANGE',
                ],
            ],
            'AttributeDefinitions' => [
                [
                    'AttributeName' => 'id',
                    'AttributeType' => 'S',
                ],
                [
                    'AttributeName' => 'version',
                    'AttributeType' => 'N',
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
            'TableName' => 'accounts',
        ]);
    }
};
