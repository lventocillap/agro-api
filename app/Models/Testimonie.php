<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonie extends Model
{
    protected $fillable = [
        'name_customer',
        'description',
        'date',
        'qualification'
    ];
}
