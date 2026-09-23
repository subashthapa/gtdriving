<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\UserController;
use \App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\BookingController as AdminBookingController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::post('/messages', [MessageController::class, 'store'])->middleware('throttle:5,1')->name('messages.store');
Route::post('book',[BookingController::class,'store'])->middleware('throttle:10,1')->name('saveBooking');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    Route::get('/bookings', [BookingController::class, 'index'])->name('bookings.index');
    Route::put('/bookings/{booking}', [BookingController::class, 'update'])->name('bookings.update');
    Route::patch('/bookings/{booking}/payment', [BookingController::class, 'updatePayment'])->name('bookings.payment.update');
    Route::delete('/bookings/{booking}', [BookingController::class, 'destroy'])->name('bookings.destroy');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:Admin|SuperAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index');
    Route::get('bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit');
    Route::put('bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update');
    Route::resource('packages', PackageController::class);
    Route::resource('pages', PageController::class);
    Route::resource('messages', MessageController::class);
});


Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified', 'role:Admin|SuperAdmin'])->prefix('dashboard/admin')->as('admin.')->group(function () {
    Route::get('instructors', [UserController::class, 'instructors'])->name('instructors.index');
    Route::get('learners', [UserController::class, 'learners'])->name('learners.index');
    Route::resource('users', UserController::class)->names('users');
});
