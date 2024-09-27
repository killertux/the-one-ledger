<?php

namespace Tests;

use App\Domain\Repository\AccountRepository;
use App\Domain\Repository\TransferRepository;
use App\Infra\Utils\Sleeper;
use Cake\Chronos\Chronos;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\Attributes\Before;

abstract class TestCase extends BaseTestCase {

    #[before]
    public function setUpChronos(): void {
        Chronos::setTestNow(Chronos::createFromTimestamp(Chronos::now()->timestamp));
    }

    public function getAccountRepository(): AccountRepository {
        return Facade::getFacadeApplication()->factory(AccountRepository::class)();
    }

    public function getTransferRepository(): TransferRepository {
        return Facade::getFacadeApplication()->factory(TransferRepository::class)();
    }

    public function getSleeper(): Sleeper {
        return new class implements Sleeper{

            public function sleep(int $milliseconds_to_sleep): void {
                // NOP
            }
        };
    }

    public function getNow(): Chronos {
        return Chronos::getTestNow();
    }
}
