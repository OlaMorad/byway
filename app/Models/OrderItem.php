<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $guarded = ['id'];
    protected $table = 'order_items';
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
