<?php declare(strict_types=1);

namespace App\Providers;

use App\Domain\Repository\TransferRepository;
use App\Infra\Repository\Transfer\DynamoDbTransferRepository;
use Aws\DynamoDb\DynamoDbClient;
use Illuminate\Support\ServiceProvider;

class DynamoDbTransferRepositoryProvider extends ServiceProvider {

    public function register(): void
    {
        $this->app->singleton(
            TransferRepository::class,
            fn() => new DynamoDbTransferRepository(app(DynamoDbClient::class))
        );
    }
}
