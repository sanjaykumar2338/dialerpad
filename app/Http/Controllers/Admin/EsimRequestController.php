<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EsimRequest;
use App\Models\EsimType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EsimRequestController extends Controller
{
    public function index(Request $request): View
    {
        $query = EsimRequest::with('type')->latest();

        $filters = [
            'status' => $request->query('status'),
            'type_id' => $request->query('type_id'),
            'search' => $request->query('search'),
        ];

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['type_id']) {
            $query->where('esim_type_id', $filters['type_id']);
        }

        if ($filters['search']) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('full_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        $requests = $query->paginate(12)->withQueryString();
        $types = EsimType::orderBy('name')->pluck('name', 'id');

        return view('admin.esim.requests.index', [
            'requests' => $requests,
            'types' => $types,
            'statuses' => EsimRequest::STATUSES,
            'filters' => $filters,
        ]);
    }

    public function edit(EsimRequest $esim_request): View
    {
        return view('admin.esim.requests.edit', [
            'requestRecord' => $esim_request->load('type'),
            'statuses' => EsimRequest::STATUSES,
            'types' => EsimType::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(Request $request, EsimRequest $esim_request): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', EsimRequest::STATUSES)],
            'notes' => ['nullable', 'string'],
            'esim_type_id' => ['nullable', 'exists:esim_types,id'],
        ]);

        $esim_request->update($data);

        return redirect()->route('admin.esim-requests.index')->with('status', 'Request updated.');
    }

    public function destroy(EsimRequest $esim_request): RedirectResponse
    {
        $esim_request->delete();

        return back()->with('status', 'Request removed.');
    }
}
