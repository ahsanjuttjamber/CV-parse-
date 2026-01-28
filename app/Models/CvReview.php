<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CvReview extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'issues',
        'candidate_email',
        'internal_email',
    ];

    protected $casts = [
        'issues' => 'array',
    ];
}
