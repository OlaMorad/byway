<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_earnings',
        'bio',
        'github_link',
        'google_link',
        'facebook_link',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
