<?php declare(strict_types=1);

namespace App\Infra\Utils;

interface Sleeper {

    public function sleep(int $milliseconds_to_sleep): void;
}
