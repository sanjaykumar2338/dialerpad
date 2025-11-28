<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallSession;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CallSessionExportController extends Controller
{
    public function export(Request $request): StreamedResponse
    {
        $fileName = 'call_sessions_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $query = CallSession::with('card');

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('card_id') && $request->card_id !== 'all') {
            $query->where('call_card_id', $request->card_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('dialed_number', 'like', "%{$search}%")
                    ->orWhere('full_number', 'like', "%{$search}%");
            });
        }

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Card Name',
                'Dialed Number',
                'Full Number',
                'Duration (s)',
                'Remaining Minutes After Call',
                'Status',
                'Created At',
            ]);

            $query->orderBy('created_at', 'desc')->chunk(500, function ($sessions) use ($handle) {
                foreach ($sessions as $session) {
                    fputcsv($handle, [
                        optional($session->card)->name,
                        $session->dialed_number,
                        $session->full_number,
                        $session->duration_seconds,
                        $session->remaining_minutes_after_call ?? optional($session->card)->remaining_minutes,
                        $session->status,
                        $session->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
