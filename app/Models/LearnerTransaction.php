<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearnerTransaction extends Model
{
    use HasFactory;

    protected $table = 'learner_transaction'; 

    protected $fillable = [
        'user_id',
        'course_id',
        'amount',
        'status',
        'method',
        'account_num',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
