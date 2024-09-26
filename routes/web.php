<?php

use App\Infra\Controller\AccountController;
use App\Infra\Controller\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/api/v1/transfer', [TransferController::class, "executeTransfers"]);
Route::get('/api/v1/transfer/{transfer_id}', [TransferController::class, "getTransfer"]);
Route::get('/api/v1/transfer/credit/{account_id}', [TransferController::class, "listTransferFromCreditAccount"]);
Route::get('/api/v1/transfer/credit/{account_id}/{version}', [TransferController::class, "getTransferFromCreditAccountAndVersion"]);
Route::get('/api/v1/transfer/debit/{account_id}', [TransferController::class, "listTransferFromDebitAccount"]);
Route::get('/api/v1/transfer/debit/{account_id}/{version}', [TransferController::class, "getTransferFromDebitAccountAndVersion"]);

Route::post('/api/v1/account', [AccountController::class, "createAccount"]);
Route::get('/api/v1/account/{account_id}/{version}', [AccountController::class, "getAccountWithVersion"]);
Route::get('/api/v1/account/{account_id}', [AccountController::class, "listAccount"]);
