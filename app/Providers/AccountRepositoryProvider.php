<?php

namespace App\Providers;

use App\Infra\Repository\Account\AccountRepository;
use App\Infra\Repository\Account\CrdbAccountRepository;
use Illuminate\Support\ServiceProvider;

class AccountRepositoryProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->singleton(AccountRepository::class, fn() => new CrdbAccountRepository());
    }
}
