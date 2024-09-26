<?php

use App\Infra\Controller\AccountController;
use App\Infra\Controller\TransferController;
use Illuminate\Support\Facades\Route;

Route::post('/api/v1/transfer', [TransferController::class, "executeTransfers"]);
Route::get('/api/v1/transfer/{transfer_id}', [TransferController::class, "getTransfer"]);
Route::get('/api/v1/transfer/creditAccount/{account_id}', [TransferController::class, "listTransferFromCreditAccount"]);
Route::get('/api/v1/transfer/creditAccount/{account_id}/{sequence}', [TransferController::class, "getTransferFromCreditAccountAndSequence"]);
Route::get('/api/v1/transfer/debitAccount/{account_id}', [TransferController::class, "listTransferFromDebitAccount"]);
Route::get('/api/v1/transfer/debitAccount/{account_id}/{sequence}', [TransferController::class, "getTransferFromDebitAccountAndSequence"]);

Route::post('/api/v1/account', [AccountController::class, "createAccount"]);
Route::get('/api/v1/account/{account_id}/{sequence}', [AccountController::class, "getAccountWithSequence"]);
Route::get('/api/v1/account/{account_id}', [AccountController::class, "listAccount"]);
