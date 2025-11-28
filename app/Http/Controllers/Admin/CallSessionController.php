<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallCard;
use App\Models\CallSession;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CallSessionController extends Controller
{
    public function index(Request $request): View
    {
        $query = CallSession::with('card')->latest();

        $filters = [
            'status' => $request->query('status'),
            'card_id' => $request->query('card_id'),
            'search' => $request->query('search'),
        ];

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['card_id'])) {
            $query->where('call_card_id', $filters['card_id']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where('dialed_number', 'like', $term)
                    ->orWhere('full_number', 'like', $term);
            });
        }

        $sessions = $query->paginate(10)->withQueryString();
        $cards = CallCard::orderBy('name')->pluck('name', 'id');
        $statuses = ['started', 'completed', 'failed', 'cancelled'];

        return view('admin.call-sessions.index', compact('sessions', 'cards', 'statuses', 'filters'));
    }
}
