<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatsController extends Controller
{
    public function trialStats()
    {
        return response()->json([
            'user' => auth()->user(),
            'trial_stats' => []
        ]);
    }

    public function advanceStats()
    {
        return response()->json([
            'user' => auth()->user(),
            'advance_stats' => []
        ]);
    }
}
