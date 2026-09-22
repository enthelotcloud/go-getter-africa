<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nomination extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomination_category_id',
        'user_id',
        'name',
        'company_or_show',
        'profile_image',
        'bio',
        'code',
        'facebook_url',
        'instagram_url',
        'twitter_url',
        'tiktok_url',
        'youtube_url',
        'website_url',
        'total_votes',
        'is_active',
        'kes_balance',
        'last_payout_phone',
        'access_pin',
    ];

    public function category()
    {
        return $this->belongsTo(NominationCategory::class, 'nomination_category_id');
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
