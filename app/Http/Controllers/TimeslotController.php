<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Timeslot;

class TimeslotController extends Controller
{
    public function getTimeslots()
    {
        $timeslots = Timeslot::all();
        return response()->json($timeslots);
    }
}
