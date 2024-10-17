<?php declare(strict_types=1);

namespace App\Providers;

use App\Domain\Repository\Transaction;
use App\Infra\Repository\DynamoDbTransaction;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\ServiceProvider;

class DynamoDbTransactionProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(Transaction::class, fn() => new DynamoDbTransaction(app(DynamoDbClient::class)));
    }
}
