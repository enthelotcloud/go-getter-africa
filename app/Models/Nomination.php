<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'nomination_category_id',
    'user_id',
    'name',
    'code',
    'company_or_show',
    'profile_image',
    'bio',
    'facebook_url',
    'instagram_url',
    'twitter_url',
    'tiktok_url',
    'youtube_url',
    'website_url',
    'total_votes',
    'is_active',
])]
class Nomination extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'total_votes' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NominationCategory::class, 'nomination_category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function poster(): HasOne
    {
        return $this->hasOne(Poster::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
