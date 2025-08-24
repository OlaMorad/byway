<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderItem extends Model
{
    use HasFactory;

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
