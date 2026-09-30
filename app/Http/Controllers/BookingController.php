<?php

namespace App\Http\Controllers;

use Auth;
use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Booking;
use App\Models\Timeslot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use App\Models\InstructorAvailability;

class BookingController extends Controller
{
    /**
     * 
     */
    public function create(Request $request)
    {
        $preferredInstructor = $request->integer('instructor') ?: null;

        return Inertia::render('Booking/Create', [
            'timeslots' => Timeslot::all(),
            'hourlyRate' => (float) config('services.instructor.hourly_rate', 60),
            'currency' => config('services.payments.currency', 'AUD'),
            'preferredInstructor' => $preferredInstructor,
            'adminMode' => false,
            'learners' => [],
        ]);
    }

    /**
     * get available timeslots
     */
    public function getAvailableSlots(Request $request)
    {
        $date = $request->query('date');
        $instructorId = $request->query('instructor');
        $duration = max(30, min(180, (int) $request->query('duration', 60)));

        if(!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        $allSlots = Timeslot::query()
            ->where('is_visible', true)
            ->get(['id', 'start_time', 'end_time']);

        if (! $instructorId) {
            if (! Role::where('name', 'Instructor')->where('guard_name', 'web')->exists()) {
                return response()->json($allSlots);
            }

            $instructors = User::role('Instructor')->select('id', 'name')->orderBy('name')->get();
            $weekday = Carbon::parse($date)->dayOfWeek;

            return response()->json($allSlots->map(function ($slot) use ($instructors, $date, $weekday, $duration) {
                $requestedEnd = Carbon::parse($slot->start_time)->addMinutes($duration)->format('H:i:s');
                $availableInstructor = $instructors->first(function ($instructor) use ($slot, $date, $weekday, $requestedEnd) {
                    return $this->instructorWorksAt($instructor->id, $weekday, $slot->start_time, $requestedEnd)
                        && ! $this->instructorHasConflict($instructor->id, $date, $slot->start_time, $requestedEnd);
                });

                if (! $availableInstructor) {
                    return null;
                }

                return [
                    'id' => $slot->id,
                    'start_time' => $slot->start_time,
                    'end_time' => $slot->end_time,
                    'available_instructor_id' => $availableInstructor->id,
                    'available_instructor_name' => $availableInstructor->name,
                ];
            })->filter()->values());
        }

        $weekday = Carbon::parse($date)->dayOfWeek;
        $available = $allSlots->filter(function ($slot) use ($instructorId, $weekday, $date, $duration) {
            $requestedEnd = Carbon::parse($slot->start_time)->addMinutes($duration)->format('H:i:s');

            return $this->instructorWorksAt((int) $instructorId, $weekday, $slot->start_time, $requestedEnd)
                && ! $this->instructorHasConflict((int) $instructorId, $date, $slot->start_time, $requestedEnd);
        })->values();

        return response()->json($available);
    }

    /**
     * 
     */
    public function index() {
        $user = Auth::user();
        $confirmed = $user && $user->hasRole('Instructor')
            ? Booking::where('instructor', $user->id)->get()
            : Booking::all();

        $instructors = User::role('Instructor')->get();
        return view('booking', [
            'confirmed' => $confirmed,
            'instructors'=>$instructors
        ]);
    }

    /**
     * 
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required_without:user_id|string|max:255',
            'email' => 'required_without:user_id|email|max:255',
            'phone' => 'required_without:user_id|string|max:20',
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'instructor' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->whereExists(function ($roleQuery) {
                        $roleQuery->selectRaw('1')
                            ->from('model_has_roles')
                            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
                            ->whereColumn('model_has_roles.model_id', 'users.id')
                            ->where('model_has_roles.model_type', User::class)
                            ->where('roles.name', 'Instructor');
                    });
                }),
            ],
            'approved_by' => 'nullable|integer',
            'learner_id' => 'nullable|integer|exists:users,id',
            'instructions' => 'nullable|string',
            'payment_method' => ['sometimes', Rule::in(['cash'])],
        ]);

        $actingUser = Auth::user();
        $isAdminBooking = $actingUser && $actingUser->hasAnyRole(['Admin', 'SuperAdmin']);

        if ($isAdminBooking && empty($validated['learner_id'])) {
            throw ValidationException::withMessages([
                'learner_id' => 'Select the learner this booking is for.',
            ]);
        }

        if ($isAdminBooking && ! empty($validated['learner_id'])) {
            $user = User::findOrFail($validated['learner_id']);
        } elseif (Auth::check()) {
            $user = Auth::user();
        } else {
            $user = User::where('email', $validated['email'])->first();

            if (! $user) {
                $user = User::create([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'],
                    'password' => Hash::make(Str::random(24)),
                ]);

                // Best effort role assignment for new learners.
                if (Role::where('name', 'Learner')->exists()) {
                    $user->assignRole('Learner');
                }
            }
        }

        $instructorId = $validated['instructor'];

        $requestedStart = Carbon::parse($validated['date'].' '.$validated['start_time']);
        $requestedEnd = Carbon::parse($validated['date'].' '.$validated['end_time']);

        if ($requestedEnd->lessThanOrEqualTo($requestedStart)) {
            throw ValidationException::withMessages([
                'end_time' => 'The booking end time must be after the start time.',
            ]);
        }

        if (! $this->instructorWorksAt(
            (int) $instructorId,
            $requestedStart->dayOfWeek,
            $requestedStart->format('H:i:s'),
            $requestedEnd->format('H:i:s')
        )) {
            throw ValidationException::withMessages([
                'start_time' => 'This lesson falls outside the instructor’s available hours.',
            ]);
        }

        $durationMinutes = $requestedStart->diffInMinutes($requestedEnd);
        $amount = round(($durationMinutes / 60) * (float) config('services.instructor.hourly_rate', 60), 2);

        DB::transaction(function () use ($validated, $user, $instructorId, $requestedStart, $requestedEnd, $amount, $isAdminBooking, $actingUser) {
            // Serialize booking creation per instructor to prevent concurrent double bookings.
            User::whereKey($instructorId)->lockForUpdate()->firstOrFail();

            $existingBooking = Booking::whereDate('start_date', $validated['date'])
                ->where('instructor', $instructorId)
                ->whereTime('start_time', '<', $requestedEnd->format('H:i:s'))
                ->whereTime('end_time', '>', $requestedStart->format('H:i:s'))
                ->exists();

            if ($existingBooking) {
                throw ValidationException::withMessages([
                    'start_time' => 'This time slot is already booked. Please choose another time.',
                ]);
            }

            Booking::create([
                'user_id' => $user->id,
                'start_time' => $validated['start_time'],
                'end_time' => $validated['end_time'],
                'start_date' => $validated['date'],
                'end_date' => $validated['date'],
                'instructor' => $instructorId,
                'approved_by' => $isAdminBooking ? $actingUser->id : ($validated['approved_by'] ?? null),
                'instructions' => $validated['instructions'] ?? null,
                'amount' => $amount,
                'payment_method' => $validated['payment_method'] ?? 'cash',
                'payment_status' => 'pending',
            ]);
        });

        return response()->json([
            'message' => 'Booking created successfully!',
            'payment' => [
                'amount' => $amount,
                'method' => $validated['payment_method'] ?? 'cash',
                'status' => 'pending',
            ],
        ]);
    }

    /**
     * 
     */
    public function getBookedDates()
    {
        $bookings = Booking::select('start_date')->distinct()->get();
        return response()->json($bookings);
    }

    /**
     * 
     */
    public function getBookingsForInstructor($id)
    {
        // Fetch bookings for the specific instructor
        $bookings = Booking::where('instructor', $id)->get();
        
        return response()->json($bookings->map(function ($booking) {
            return [
                'id' => $booking->id,
                'title' => 'Unavailable',
                'start' => Carbon::parse($booking->start_date)->toDateString(). 'T' . $booking->start_time,
                'end' => Carbon::parse($booking->end_date)->toDateString(). 'T' . $booking->end_time,
                'instructor' => $booking->instructor
            ];
        }));
    }

    public function fetchInstructors()
    {
        if (! Role::where('name', 'Instructor')->where('guard_name', 'web')->exists()) {
            return response()->json([]);
        }

        return response()->json(
            User::role('Instructor')->select('id', 'name')->orderBy('name')->get()
        );
    }

    private function instructorWorksAt(int $instructorId, int $weekday, string $startTime, string $endTime): bool
    {
        $hasAvailability = InstructorAvailability::where('instructor_id', $instructorId)->exists();

        if (! $hasAvailability) {
            return true;
        }

        return InstructorAvailability::query()
            ->where('instructor_id', $instructorId)
            ->where('weekday', $weekday)
            ->where('is_active', true)
            ->whereTime('start_time', '<=', $startTime)
            ->whereTime('end_time', '>=', $endTime)
            ->exists();
    }

    private function instructorHasConflict(int $instructorId, string $date, string $startTime, string $endTime): bool
    {
        return Booking::query()
            ->whereDate('start_date', $date)
            ->where('instructor', $instructorId)
            ->whereTime('start_time', '<', $endTime)
            ->whereTime('end_time', '>', $startTime)
            ->exists();
    }

    /**
     * Update booking
     */
    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    
        $validated = $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|same:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructions' => 'nullable|string',
        ]);

        $hasConflict = Booking::query()
            ->whereKeyNot($booking->id)
            ->where('instructor', $booking->instructor)
            ->whereDate('start_date', $validated['start_date'])
            ->whereTime('start_time', '<', $validated['end_time'])
            ->whereTime('end_time', '>', $validated['start_time'])
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'start_time' => 'This time overlaps another booking.',
            ]);
        }

        $start = Carbon::parse($validated['start_date'].' '.$validated['start_time']);
        $end = Carbon::parse($validated['end_date'].' '.$validated['end_time']);
        $validated['amount'] = round(
            ($start->diffInMinutes($end) / 60) * (float) config('services.instructor.hourly_rate', 60),
            2
        );

        $booking->update($validated);
    
        return back()->with('success', 'Booking updated.');
    }  
    
    /**
     * Destroy booking
     */
    public function destroy(Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $bookingStart = Carbon::parse($booking->start_date.' '.$booking->start_time);
        if ($bookingStart->isPast()) {
            throw ValidationException::withMessages([
                'booking' => 'Past bookings cannot be cancelled.',
            ]);
        }


        if ($booking->payment_status === 'paid') {
            throw ValidationException::withMessages([
                'booking' => 'Paid bookings must be refunded before they can be cancelled.',
            ]);
        }

        $booking->delete();
    
        return back()->with('success', 'Booking deleted.');
    }

    public function updatePayment(Request $request, Booking $booking)
    {
        $user = $request->user();
        $canManagePayment = (int) $booking->instructor === (int) $user->id
            || $user->hasAnyRole(['Admin', 'SuperAdmin']);

        abort_unless($canManagePayment, 403, 'Unauthorized');

        $validated = $request->validate([
            'payment_status' => ['required', Rule::in(['pending', 'paid', 'refunded'])],
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $booking->update([
            'payment_status' => $validated['payment_status'],
            'paid_at' => $validated['payment_status'] === 'paid' ? now() : null,
            'payment_reference' => $validated['payment_reference'] ?? null,
        ]);

        $instructor = User::find($booking->instructor);
        if ($instructor) {
            $instructor->update([
                'income' => Booking::where('instructor', $instructor->id)
                    ->where('payment_status', 'paid')
                    ->sum('amount'),
            ]);
        }

        return back()->with('success', 'Payment status updated.');
    }
}
