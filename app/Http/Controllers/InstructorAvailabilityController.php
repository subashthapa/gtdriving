<?php

namespace App\Http\Controllers;

use App\Models\InstructorAvailability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class InstructorAvailabilityController extends Controller
{
    public function edit(Request $request)
    {
        return Inertia::render('Instructor/Availability', [
            'availability' => InstructorAvailability::query()
                ->where('instructor_id', $request->user()->id)
                ->orderBy('weekday')
                ->get(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'days' => 'required|array|size:7',
            'days.*.weekday' => 'required|integer|between:0,6',
            'days.*.is_active' => 'required|boolean',
            'days.*.start_time' => 'required|date_format:H:i',
            'days.*.end_time' => 'required|date_format:H:i|after:days.*.start_time',
        ]);

        DB::transaction(function () use ($request, $validated) {
            InstructorAvailability::where('instructor_id', $request->user()->id)->delete();

            foreach ($validated['days'] as $day) {
                InstructorAvailability::create([
                    'instructor_id' => $request->user()->id,
                    ...$day,
                ]);
            }
        });

        return back()->with('success', 'Availability updated.');
    }
}
