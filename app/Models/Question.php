<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question',
        'answer'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    
}
