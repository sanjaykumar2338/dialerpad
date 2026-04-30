<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Services\DistributionDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function index(Request $request, DistributionDashboardService $dashboard): View
    {
        $includeAllAccounts = (bool) $request->user()->is_admin;
        $batches = $dashboard->scopeAccount(
            Batch::with(['account', 'request'])->latest(),
            $request->user(),
            $includeAllAccounts
        )->paginate(12);

        $batches->setCollection(
            $batches->getCollection()->map(fn (Batch $batch) => $dashboard->formatBatch($batch))
        );

        return view('account.batches.index', [
            'batches' => $batches,
        ]);
    }
}
