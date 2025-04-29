<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'lastname',
        'cellphone',
        'disctric',
        'email', 
        'active',
        'message'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
