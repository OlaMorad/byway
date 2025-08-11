<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstructorTransaction extends Model
{
    use HasFactory;

    protected $table = 'instructor_transaction';

    protected $fillable = [
        'user_id',
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
}
