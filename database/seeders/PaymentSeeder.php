<?php

namespace Database\Seeders;

use App\Models\Payment;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // نوزع البيانات على السنوات 2022 → 2025
        foreach (range(2022, 2025) as $year) {
            foreach (range(1, 12) as $month) {
                // مثلاً لكل شهر نولد 5 مدفوعات
                Payment::factory()->count(5)->create([
                    'created_at' => now()->setDate($year, $month, rand(1, 28)),
                ]);
            }
        }
    }
}
