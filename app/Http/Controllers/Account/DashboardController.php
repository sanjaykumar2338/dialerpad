<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\DistributionDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, DistributionDashboardService $dashboard): View|JsonResponse
    {
        if ($request->expectsJson() || $request->wantsJson()) {
            return response()->json($dashboard->payload($request->user()));
        }

        return view('account.dashboard', $dashboard->payload($request->user()));
    }
}
