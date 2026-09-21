<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'nomination_id',
    'original_image',
    'processed_image',
    'final_poster_path',
    'vote_url',
    'voting_start_date',
    'voting_end_date',
    'template_settings',
])]
class Poster extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'voting_start_date' => 'datetime',
            'voting_end_date' => 'datetime',
            'template_settings' => 'array',
        ];
    }

    public function nomination(): BelongsTo
    {
        return $this->belongsTo(Nomination::class);
    }
}
