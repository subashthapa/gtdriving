<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimeslotsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Set the starting time (07:00) and ending time (19:00)
        $startTime = Carbon::createFromTime(7,0,0);
        $endTime = Carbon::createFromTime(19,0,0);
        $interval = 30; // interval in minutes

        // Loop through time intervals until reaching the end time
        while ($startTime < $endTime) {
            $slotEnd = $startTime->copy()->addMinutes($interval);
            DB::table('timeslots')->insert([
                'start_time' => $startTime->format('H:i:s'),
                'end_time'   => $slotEnd->format('H:i:s'),
                'is_visible' => true, // Mark as visible by default
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Move to the next slot
            $startTime->addMinutes($interval);
        }
    }
}
