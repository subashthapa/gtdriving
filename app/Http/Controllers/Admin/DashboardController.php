<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Page;
use App\Models\Package;
use App\Models\User;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        $futureBookings = Booking::with('user')
            ->whereDate('start_date', '>=', Carbon::today())
            ->orderBy('start_date', 'asc')
            ->limit(10)
            ->get();

        return response()->json([
            'total_users' => User::count(),
            'total_bookings' => Booking::count(),
            'total_pages' => Page::count(),
            'total_packages' => Package::count(),
            'upcoming_bookings' => $futureBookings,
        ]);
    }
}
