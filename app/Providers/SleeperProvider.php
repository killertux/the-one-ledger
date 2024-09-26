<?php declare(strict_types=1);

namespace App\Providers;

use App\Infra\Utils\PhpUsleepSleeper;
use App\Infra\Utils\Sleeper;
use Illuminate\Support\ServiceProvider;

class SleeperProvider extends ServiceProvider {

    public function register(): void
    {
        $this->app->singleton(Sleeper::class, fn() => new PhpUsleepSleeper());
    }
}
