<?php

namespace App\Http\Controllers\Admin;

use Auth;
use App\Models\Booking;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $today = now()->toDateString();
        $isStudent = $user->hasRole('Learner');
        $isInstructor = $user->hasRole('Instructor');
        $isAdmin = $user->hasRole('Admin') || $user->hasRole('SuperAdmin');

        $pastBookings = $isStudent
            ? Booking::where('user_id', $user->id)
                ->with('instructorUser:id,name')
                ->whereDate('end_date', '<', $today)
                ->orderBy('start_date', 'desc')
                ->get()
            : collect();

        $futureBookings = $isStudent
            ? Booking::where('user_id', $user->id)
                ->with('instructorUser:id,name')
                ->whereDate('start_date', '>=', $today)
                ->orderBy('start_date', 'asc')
                ->get()
            : collect();

        $instructorPastBookings = collect();
        $instructorFutureBookings = collect();
        $instructorLearners = collect();
        $instructorStats = [
            'hourly_rate' => (float) config('services.instructor.hourly_rate', 0),
            'total_earnings' => 0.0,
            'outstanding_payments' => 0.0,
            'total_lessons' => 0,
            'total_learners' => 0,
            'past_lessons' => 0,
            'upcoming_lessons' => 0,
        ];

        if ($isInstructor) {
            $instructorBookings = Booking::query()
                ->where('instructor', $user->id)
                ->with('user:id,name,email,phone')
                ->orderBy('start_date', 'asc')
                ->get();

            $instructorPastBookings = $instructorBookings
                ->filter(fn ($booking) => $booking->end_date < $today)
                ->values();

            $instructorFutureBookings = $instructorBookings
                ->filter(fn ($booking) => $booking->start_date >= $today)
                ->values();

            $instructorLearners = $instructorBookings
                ->filter(fn ($booking) => ! empty($booking->user_id))
                ->groupBy('user_id')
                ->map(function (Collection $bookings) {
                    $learner = $bookings->first()->user;

                    return [
                        'id' => $learner?->id,
                        'name' => $learner?->name,
                        'email' => $learner?->email,
                        'phone' => $learner?->phone,
                        'lessons_count' => $bookings->count(),
                    ];
                })
                ->filter(fn ($learner) => ! empty($learner['id']))
                ->values();

            $hourlyRate = (float) config('services.instructor.hourly_rate', 0);

            $instructorStats = [
                'hourly_rate' => $hourlyRate,
                'total_earnings' => (float) $instructorBookings
                    ->where('payment_status', 'paid')->sum('amount'),
                'outstanding_payments' => (float) $instructorBookings
                    ->where('payment_status', 'pending')->sum('amount'),
                'total_lessons' => $instructorBookings->count(),
                'total_learners' => $instructorLearners->count(),
                'past_lessons' => $instructorPastBookings->count(),
                'upcoming_lessons' => $instructorFutureBookings->count(),
            ];
        }

        return Inertia::render('Dashboard', [
            'isStudent' => $isStudent,
            'isInstructor' => $isInstructor,
            'isAdmin' => $isAdmin,
            'pastBookings' => $pastBookings,
            'futureBookings' => $futureBookings,
            'instructorPastBookings' => $instructorPastBookings,
            'instructorFutureBookings' => $instructorFutureBookings,
            'instructorLearners' => $instructorLearners,
            'instructorStats' => $instructorStats,
        ]);

    }
}
