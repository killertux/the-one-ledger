<?php

namespace App\Providers;

use App\Domain\Repository\TransferRepository;
use App\Infra\Repository\Transfer\CrdbTransferRepository;
use Illuminate\Support\ServiceProvider;

class CrdbTransferRepositoryProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TransferRepository::class, fn() => new CrdbTransferRepository());
    }

}
