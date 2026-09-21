<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MpesaController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('mpesa')->group(function () {
    Route::post('/stk-callback', [MpesaController::class, 'stkCallback']);
    Route::post('/b2c-result',   [MpesaController::class, 'b2cResult']);
    Route::post('/b2c-timeout',  [MpesaController::class, 'b2cTimeout']);
    Route::post('/validate',     [MpesaController::class, 'validatePayment']);
    Route::post('/confirm',      [MpesaController::class, 'confirmPayment']);
});
