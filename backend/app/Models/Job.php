<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'category',
        'company',
        'level',
        'skill',
        'type',
        'salary',
        'location',
        'poster',
        'job_description',
        'requirements',
        'responsibilities',
        'benefits',
        'experience',
    ];
}
