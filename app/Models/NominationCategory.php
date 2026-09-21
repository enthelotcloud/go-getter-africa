<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'slug', 'thumbnail', 'excerpt', 'description', 'is_active'])]
class NominationCategory extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function nominations(): HasMany
    {
        return $this->hasMany(Nomination::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
