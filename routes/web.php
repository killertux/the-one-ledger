<?php

use App\Infra\Controller\AccountController;
use App\Infra\Controller\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/api/v1/transfer', [TransferController::class, "executeTransfers"]);

Route::post('/api/v1/account', [AccountController::class, "createAccount"]);
Route::get('/api/v1/account/{account_id}/{sequence}', [AccountController::class, "getAccountWithSequence"]);
Route::get('/api/v1/account/{account_id}', [AccountController::class, "listAccount"]);

Route::get('check', function () {
    $account_ids = \Illuminate\Support\Facades\DB::select('SELECT DISTINCT id FROM accounts;');
    $account_ids = array_map(fn($account) => $account->id, $account_ids);
    $repository = new \App\Infra\Repository\Account\CrdbAccountRepository();
    $data = [];
    $sum_debit = 0;
    $sum_credit = 0;
    foreach ($account_ids as $account_id) {
        $account = $repository->getAccount($account_id);
        $data['accounts'][] = [
            'id' => $account->getId(),
            'sequence' => $account->getSequence(),
            'debit' => $account->getDebitAmount()->getAmount(),
            'credit' => $account->getCreditAmount()->getAmount(),
            'datetime' => $account->getDatetime()?->toDateTimeString(),
        ];
        $sum_debit += $account->getDebitAmount()->getAmount();
        $sum_credit += $account->getCreditAmount()->getAmount();
    }
    $data['sum_debit'] = $sum_debit;
    $data['sum_credit'] = $sum_credit;
    return response()->json($data);
});
