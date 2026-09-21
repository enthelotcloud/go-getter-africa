<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    protected $fillable = [
        'user_id', 'guest_phone', 'nomination_id', 'nomination_category_id',
        'tokens_spent', 'commission_earned_kes', 'transaction_id', 'ip_address'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nomination()
    {
        return $this->belongsTo(Nomination::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
