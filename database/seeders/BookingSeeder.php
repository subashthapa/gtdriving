<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    public function run(): void
    {
        $hourlyRate = (float) config('services.instructor.hourly_rate', 60);
        $instructors = User::role('Instructor')->orderBy('id')->get();
        $learners = User::role('Learner')->orderBy('id')->get();

        if ($instructors->isEmpty() || $learners->isEmpty()) {
            return;
        }

        $baseDate = Carbon::today();

        $seedBookings = [
            [
                'user_id' => $learners[0]->id,
                'instructor' => $instructors[0]->id,
                'start_date' => $baseDate->copy()->subDays(5)->toDateString(),
                'end_date' => $baseDate->copy()->subDays(5)->toDateString(),
                'start_time' => '09:00',
                'end_time' => '11:00',
                'instructions' => '1',
                'amount' => $hourlyRate * 2,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_at' => $baseDate->copy()->subDays(5),
            ],
            [
                'user_id' => $learners[1 % $learners->count()]->id,
                'instructor' => $instructors[0]->id,
                'start_date' => $baseDate->copy()->subDays(2)->toDateString(),
                'end_date' => $baseDate->copy()->subDays(2)->toDateString(),
                'start_time' => '13:00',
                'end_time' => '15:00',
                'instructions' => '2',
                'amount' => $hourlyRate * 2,
                'payment_method' => 'cash',
                'payment_status' => 'paid',
                'paid_at' => $baseDate->copy()->subDays(2),
            ],
            [
                'user_id' => $learners[2 % $learners->count()]->id,
                'instructor' => $instructors[0]->id,
                'start_date' => $baseDate->copy()->addDays(2)->toDateString(),
                'end_date' => $baseDate->copy()->addDays(2)->toDateString(),
                'start_time' => '10:00',
                'end_time' => '12:00',
                'instructions' => '3',
                'amount' => $hourlyRate * 2,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
            ],
            [
                'user_id' => $learners[0]->id,
                'instructor' => $instructors[1 % $instructors->count()]->id,
                'start_date' => $baseDate->copy()->addDays(3)->toDateString(),
                'end_date' => $baseDate->copy()->addDays(3)->toDateString(),
                'start_time' => '14:00',
                'end_time' => '16:00',
                'instructions' => '4',
                'amount' => $hourlyRate * 2,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
            ],
            [
                'user_id' => $learners[1 % $learners->count()]->id,
                'instructor' => $instructors[1 % $instructors->count()]->id,
                'start_date' => $baseDate->copy()->addDays(7)->toDateString(),
                'end_date' => $baseDate->copy()->addDays(7)->toDateString(),
                'start_time' => '08:00',
                'end_time' => '10:00',
                'instructions' => '5',
                'amount' => $hourlyRate * 2,
                'payment_method' => 'cash',
                'payment_status' => 'pending',
            ],
        ];

        foreach ($seedBookings as $booking) {
            Booking::create($booking);
        }
    }
}
