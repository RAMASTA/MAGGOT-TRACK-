<?php

namespace App\Http\Controllers;

use App\Services\DashboardStatsService;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(DashboardStatsService $stats)
    {
        return view('dashboard', $stats->build(Auth::id()));
    }
}
