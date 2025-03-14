<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['title', 'description','features'];

    protected $attributes = [
        'features' => '[]', // Guarda un array vacío como string JSON
    ];    

    protected $hidden = ['created_at', 'updated_at'];

    protected $casts = [
        'features' => 'array',
    ];

}
