<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\TimeslotController;
use App\Http\Controllers\Admin\DashboardController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Save new booking
// Route::post('/booking', [BookingController::class, 'store']);

// Route::get('/bookings', [BookingController::class, 'fetchBookings']);
Route::get('/instructors', [BookingController::class, 'fetchInstructors']);
Route::get('/instructors/{id}/bookings', [BookingController::class, 'getBookingsForInstructor'])->middleware('throttle:60,1');

Route::get('available-slots', [BookingController::class, 'getAvailableSlots']);
// Route::post('book', [BookingController::class, 'store'])->middleware('auth');
Route::get('booked-dates', [BookingController::class, 'getBookedDates'])->middleware('throttle:60,1');
Route::get('get-time-slots', [TimeslotController::class, 'getTimeslots']);

Route::middleware(['auth', 'role:SuperAdmin|Admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [DashboardController::class, 'stats']);
});
