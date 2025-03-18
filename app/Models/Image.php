<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Image extends Model
{
    protected $fillable = [
        'imageble_type',
        'imageble_id',
        'url'
    ];
    protected $hidden = ['imageble_id'];
    
    public function imageble(): MorphTo
    {
        return $this->morphTo();
    }
}
