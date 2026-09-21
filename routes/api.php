<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// STK Push Callback
Route::post('/mpesa/stk-callback', function (Request $request) {
    Log::info('STK Callback received:', $request->all());

    $data = $request->json('Body.stkCallback');

    if ($data['ResultCode'] == 0) {
        // Payment successful
        $meta = $data['CallbackMetadata']['Item'];
        $receipt = collect($meta)->firstWhere('Name', 'MpesaReceiptNumber')['Value'];

        // Find the pending transaction using an identifier you pass during creation
        // and update it to 'completed' with the $receipt.
    }

    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
});

// B2C Callbacks
Route::post('/mpesa/b2c-result', function (Request $request) {
    Log::info('B2C Result:', $request->all());
    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
});

Route::post('/mpesa/b2c-timeout', function (Request $request) {
    Log::info('B2C Timeout:', $request->all());
    return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
});
