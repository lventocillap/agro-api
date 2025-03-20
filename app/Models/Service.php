<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Service extends Model
{
    protected $fillable = ['title', 'description','features'];

    protected $hidden = ['created_at', 'updated_at'];

    public function image(): MorphOne
    {
        return $this->morphOne(Image::class, 'imageble');
    }

}
