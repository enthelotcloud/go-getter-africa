<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'user_id',
        'phone_number',
        'amount',
        'tokens_bought',
        'nomination_id',
        'merchant_request_id',
        'checkout_request_id',
        'receipt_number',
        'result_desc',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nomination()
    {
        return $this->belongsTo(Nomination::class);
    }
}
