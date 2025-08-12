<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $table = 'payment_methods';
    protected $guarded = ['id'];


    protected $casts = [
        'billing_details' => 'encrypted:array',
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getMaskedAttribute()
    {
        return $this->last4 ? '**** **** **** '.$this->last4 : null;
    }
}
