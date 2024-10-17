<?php declare(strict_types=1);

namespace App\Infra\Repository;

use App\Domain\Repository\Transaction;
use Illuminate\Support\Facades\DB;

class CrdbTransaction implements Transaction {

    public function begin(): void {
        DB::beginTransaction();
    }

    public function commit(): void {
        DB::commit();
    }

    public function rollback(): void {
        DB::rollBack();
    }
}
