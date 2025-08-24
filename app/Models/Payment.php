<?php

namespace App\Models;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'payments';

    protected $casts = [
        'response_payload' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function order()
    {
        return $this->hasOne(Order::class);
    }

    public function userPaymentMethod()
    {
        return $this->hasOne(PaymentMethod::class, 'user_id', 'user_id');
    }
}
