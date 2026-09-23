<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();

        $query = Booking::query()
            ->with(['user:id,name,email,phone', 'instructorUser:id,name'])
            ->orderBy('start_date', 'desc')
            ->orderBy('start_time', 'desc');

        if ($status === 'upcoming') {
            $query->whereDate('start_date', '>=', today());
        } elseif ($status === 'past') {
            $query->whereDate('start_date', '<', today());
        } elseif (in_array($status, ['pending', 'paid', 'refunded'], true)) {
            $query->where('payment_status', $status);
        }

        return Inertia::render('Admin/Bookings/Index', [
            'bookings' => $query->paginate(20)->withQueryString(),
            'filters' => ['status' => $status],
        ]);
    }

    public function edit(Booking $booking)
    {
        return Inertia::render('Admin/Bookings/Edit', [
            'booking' => $booking->load(['user:id,name,email,phone', 'instructorUser:id,name']),
            'instructors' => User::role('Instructor')->select('id', 'name')->orderBy('name')->get(),
            'learners' => User::role('Learner')->select('id', 'name', 'email')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')],
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
            'start_date' => 'required|date',
            'end_date' => 'required|date|same:start_date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'instructions' => 'nullable|string',
            'payment_status' => ['required', Rule::in(['pending', 'paid', 'refunded'])],
            'payment_reference' => 'nullable|string|max:255',
        ]);

        $hasConflict = Booking::query()
            ->whereKeyNot($booking->id)
            ->where('instructor', $validated['instructor'])
            ->whereDate('start_date', $validated['start_date'])
            ->whereTime('start_time', '<', $validated['end_time'])
            ->whereTime('end_time', '>', $validated['start_time'])
            ->exists();

        if ($hasConflict) {
            throw ValidationException::withMessages([
                'start_time' => 'This time overlaps another booking for the selected instructor.',
            ]);
        }

        $oldInstructorId = $booking->instructor;
        $start = Carbon::parse($validated['start_date'].' '.$validated['start_time']);
        $end = Carbon::parse($validated['end_date'].' '.$validated['end_time']);
        $validated['amount'] = round(
            ($start->diffInMinutes($end) / 60) * (float) config('services.instructor.hourly_rate', 60),
            2
        );
        $validated['paid_at'] = $validated['payment_status'] === 'paid'
            ? ($booking->paid_at ?? now())
            : null;

        $booking->update($validated);

        $this->refreshInstructorIncome($oldInstructorId);
        $this->refreshInstructorIncome($booking->instructor);

        return redirect()->route('admin.bookings.index')->with('success', 'Booked session updated.');
    }

    private function refreshInstructorIncome(?int $instructorId): void
    {
        if (! $instructorId) {
            return;
        }

        User::whereKey($instructorId)->update([
            'income' => Booking::where('instructor', $instructorId)
                ->where('payment_status', 'paid')
                ->sum('amount'),
        ]);
    }
}
