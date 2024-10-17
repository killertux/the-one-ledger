<?php declare(strict_types=1);

namespace App\Providers;

use App\Domain\Repository\Transaction;
use App\Infra\Repository\CrdbTransaction;
use Illuminate\Support\ServiceProvider;

class CrdbTransactionProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(Transaction::class, fn() => new CrdbTransaction());
    }
}
