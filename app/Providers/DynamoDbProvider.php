<?php declare(strict_types=1);

namespace App\Providers;

use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\ServiceProvider;

class DynamoDbProvider extends ServiceProvider {

    public function register(): void
    {
        $this->app->singleton(DynamoDbClient::class, fn() => new DynamoDbClient([
            'region' => 'us-west-2',
            'version' => 'latest',
            'endpoint' => 'http://dynamodb:8000',
            'credentials' => [
                'key' => 'AKIAIOSFODNN7EXAMPLE',
                'secret' => 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
            ],
        ]));
    }
}
