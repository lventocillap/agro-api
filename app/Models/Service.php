<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

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

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageable');
    }

}
