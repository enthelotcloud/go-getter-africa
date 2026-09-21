<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'user_id', 'type', 'phone_number', 'amount', 'merchant_request_id',
        'checkout_request_id', 'receipt_number', 'status', 'result_desc',
        'nomination_id', 'tokens_bought'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
