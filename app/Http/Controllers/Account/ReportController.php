<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Services\DistributionDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request, DistributionDashboardService $dashboard): View
    {
        return view('account.reports.index', $dashboard->reports($request->user()));
    }
}
