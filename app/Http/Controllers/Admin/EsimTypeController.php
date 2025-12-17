<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EsimType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EsimTypeController extends Controller
{
    public function index(): View
    {
        $types = EsimType::latest()->paginate(10);

        return view('admin.esim.types.index', compact('types'));
    }

    public function create(): View
    {
        return view('admin.esim.types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_id' => ['required', 'string', 'max:255'],
            'provider_reference_code' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        EsimType::create($data);

        return redirect()->route('admin.esim-types.index')->with('status', 'eSIM type created.');
    }

    public function edit(EsimType $esim_type): View
    {
        return view('admin.esim.types.edit', ['type' => $esim_type]);
    }

    public function update(Request $request, EsimType $esim_type): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_id' => ['required', 'string', 'max:255'],
            'provider_reference_code' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $esim_type->update($data);

        return redirect()->route('admin.esim-types.index')->with('status', 'eSIM type updated.');
    }

    public function destroy(EsimType $esim_type): RedirectResponse
    {
        $esim_type->delete();

        return redirect()->route('admin.esim-types.index')->with('status', 'eSIM type deleted.');
    }
}
