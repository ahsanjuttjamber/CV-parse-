<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cv extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_name',
        'file_path',
        'parsed_data'
    ];

    protected $casts = [
        'parsed_data' => 'array'
    ];
}
