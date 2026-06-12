<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'thumbnail',
        'gallery_images',
        'status',
        'type',
        'project_date',
        'featured',
        'excerpt'
    ];

    protected $casts = [
        'gallery_images' => 'array',
        'project_date' => 'date',
    ];
}