<?php declare(strict_types=1);

namespace App\Infra\Repository\Transfer;

use App\Domain\Transfer;

interface TransferRepository {

    /** @param Transfer[] $transfers */
    public function createTransfers(array $transfers): void;
}
