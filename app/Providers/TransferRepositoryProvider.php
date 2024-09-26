<?php

namespace App\Providers;

use App\Infra\Repository\Transfer\CrdbTransferRepository;
use App\Infra\Repository\Transfer\TransferRepository;
use Illuminate\Support\ServiceProvider;

class TransferRepositoryProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TransferRepository::class, fn() => new CrdbTransferRepository());
    }

}
