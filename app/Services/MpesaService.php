<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Switch between sandbox and live automatically based on your .env
        $this->baseUrl = env('MPESA_ENV') === 'live'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    /**
     * Generate OAuth Token
     */
    public function getAccessToken()
    {
        $credentials = base64_encode(env('MPESA_CONSUMER_KEY') . ':' . env('MPESA_CONSUMER_SECRET'));

        $response = Http::withHeaders(['Authorization' => 'Basic ' . $credentials])
            ->get($this->baseUrl . '/oauth/v1/generate?grant_type=client_credentials');

        return $response->json()['access_token'] ?? null;
    }

    /**
     * Initiate STK Push (Buy Coffee)
     */
    public function stkPush($phone, $amount, $reference)
    {
        // Format phone to 254XXXXXXXXX
        $phone = '254' . substr(preg_replace('/[^0-9]/', '', $phone), -9);
        $timestamp = date('YmdHis');
        $password = base64_encode(env('MPESA_PAYBILL_SHORTCODE') . env('MPESA_PASSKEY') . $timestamp);

        $response = Http::withToken($this->getAccessToken())->post($this->baseUrl . '/mpesa/stkpush/v1/processrequest', [
            'BusinessShortCode' => env('MPESA_PAYBILL_SHORTCODE'),
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline', // Or CustomerBuyGoodsOnline for Till
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => env('MPESA_PAYBILL_SHORTCODE'),
            'PhoneNumber' => $phone,
            'CallBackURL' => url('/api/mpesa/stk-callback'),
            'AccountReference' => $reference,
            'TransactionDesc' => 'Vote for user - GGA'
        ]);

        return $response->json();
    }

    /**
     * Initiate B2C (Admin Withdrawal)
     */
    public function withdrawB2C($phone, $amount, $remarks = 'Admin Withdrawal')
    {
        $phone = '254' . substr(preg_replace('/[^0-9]/', '', $phone), -9);

        // B2C requires you to encrypt the initiator password using Safaricom's public cert
        $pubKey = file_get_contents(storage_path('cert/ProductionCertificate.cer'));
        openssl_public_encrypt(env('MPESA_INITIATOR_PASSWORD'), $encryptedPassword, $pubKey, OPENSSL_PKCS1_PADDING);
        $securityCredential = base64_encode($encryptedPassword);

        $response = Http::withToken($this->getAccessToken())->post($this->baseUrl . '/mpesa/b2c/v1/paymentrequest', [
            'InitiatorName' => env('MPESA_INITIATOR_NAME'),
            'SecurityCredential' => $securityCredential,
            'CommandID' => 'BusinessPayment', // B2C Transfer
            'Amount' => $amount,
            'PartyA' => env('MPESA_B2C_SHORTCODE'),
            'PartyB' => $phone,
            'Remarks' => $remarks,
            'QueueTimeOutURL' => url('/api/mpesa/b2c-timeout'),
            'ResultURL' => url('/api/mpesa/b2c-result'),
            'Occasion' => 'Wallet Withdrawal'
        ]);

        return $response->json();
    }
}
