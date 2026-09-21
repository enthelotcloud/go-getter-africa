<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPackage extends Model
{
    protected $fillable = ['name', 'tokens', 'price_kes', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_kes' => 'decimal:2',
        ];
    }
}
