<?php declare(strict_types=1);

namespace App\Infra\Utils;

class PhpUsleepSleeper implements Sleeper {

    public function sleep(int $milliseconds_to_sleep): void {
        usleep($milliseconds_to_sleep * 1000);
    }
}
