<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallCard;
use App\Models\CallSession;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $activeCards = CallCard::where('status', 'active')->count();
        $expiredCards = CallCard::where('status', 'expired')->count();
        $totalMinutesSold = CallCard::sum('total_minutes');
        $totalMinutesUsed = CallCard::sum('used_minutes');
        $totalCalls = CallSession::count();

        return view('admin.dashboard', [
            'active_cards' => $activeCards,
            'expired_cards' => $expiredCards,
            'total_minutes_sold' => $totalMinutesSold,
            'total_minutes_used' => $totalMinutesUsed,
            'total_calls' => $totalCalls,
        ]);
    }
}
