<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'video_url',
        'course_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
