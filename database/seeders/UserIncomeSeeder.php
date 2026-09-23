<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserIncomeSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->update(['income' => 0]);

        $incomeByInstructor = Booking::query()
            ->where('payment_status', 'paid')
            ->get()
            ->groupBy('instructor')
            ->map(fn ($bookings) => round($bookings->sum('amount'), 2));

        foreach ($incomeByInstructor as $instructorId => $income) {
            if (! $instructorId) {
                continue;
            }

            User::where('id', (int) $instructorId)->update(['income' => $income]);
        }
    }
}
