<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'nomination_id',
    'nomination_category_id',
    'tokens_spent',
    'ip_address',
    'user_agent',
])]
class Vote extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'tokens_spent' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function nomination(): BelongsTo
    {
        return $this->belongsTo(Nomination::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NominationCategory::class, 'nomination_category_id');
    }
}
