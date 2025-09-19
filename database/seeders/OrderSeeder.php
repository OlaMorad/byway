<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // إنشاء 20 أوردر
        Order::factory()->count(100)->create()->each(function ($order) {
            // لكل أوردر نضيف 1-5 OrderItems
            OrderItem::factory()->count(rand(1, 5))->create([
                'order_id' => $order->id,
            ]);
        });
    }
}
