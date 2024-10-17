<?php declare(strict_types=1);

namespace App\Providers;

use App\Domain\Repository\AccountRepository;
use App\Infra\Repository\Account\DynamoDbAccountRepository;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\ServiceProvider;

class DynamoDbAccountRepositoryProvider extends ServiceProvider {

    public function register(): void
    {
        $this->app->singleton(
            AccountRepository::class,
            fn() => new DynamoDbAccountRepository(app(DynamoDbClient::class))
        );
    }
}
