<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AboutUs extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 
        'mission', 
        'vision', 
        'values', 
        'name_yt', 
        'url_yt'];

    protected $casts = [
        'values' => 'array',
    ];
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
