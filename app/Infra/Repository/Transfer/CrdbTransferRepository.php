<?php declare(strict_types=1);

namespace App\Infra\Repository\Transfer;

use App\Domain\Transfer;
use Illuminate\Support\Facades\DB;

class CrdbTransferRepository implements TransferRepository {

    /** @param Transfer[] $transfers */
    public function createTransfers(array $transfers): void {
        DB::table('transfers')->insert(array_map(function($transfer) {
            return [
                'id' => $transfer->getId(),
                'debit_account_id' => $transfer->getDebitAccountId(),
                'debit_sequence' => $transfer->getDebitAccountSequence(),
                'credit_account_id' => $transfer->getCreditAccountId(),
                'credit_sequence' => $transfer->getCreditAccountSequence(),
                'currency' => $transfer->getAmount()->getCurrency(),
                'amount' => $transfer->getAmount()->getAmount(),
                'metadata' => json_encode($transfer->getMetadata()),
                'created_at' => $transfer->getCreatedAt(),
            ];
        }, $transfers));
    }
}
