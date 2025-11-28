<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EsimCode;
use App\Models\EsimType;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class EsimCodeController extends Controller
{
    public function index(): View
    {
        $codes = EsimCode::with('type')->latest()->paginate(20);

        return view('admin.esim.codes.index', compact('codes'));
    }

    public function create(): View
    {
        $types = EsimType::orderBy('name')->get();

        return view('admin.esim.codes.create', compact('types'));
    }

    public function store(Request $request, QrCodeService $qr): RedirectResponse
    {
        $data = $request->validate([
            'esim_type_id' => ['required', 'exists:esim_types,id'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        $code = EsimCode::create([
            'uuid' => (string) Str::uuid(),
            'esim_type_id' => $data['esim_type_id'],
            'label' => $data['label'] ?? null,
            'status' => 'active',
        ]);

        $url = url('/esim/' . $code->uuid);
        $qr->generateForEsimCode($code, $url);

        return redirect()->route('admin.esim-codes.index')->with('success', 'eSIM QR created successfully.');
    }

    public function edit(EsimCode $esimCode): View
    {
        $types = EsimType::orderBy('name')->get();

        return view('admin.esim.codes.edit', [
            'code' => $esimCode,
            'types' => $types,
        ]);
    }

    public function update(Request $request, EsimCode $esimCode): RedirectResponse
    {
        $data = $request->validate([
            'esim_type_id' => ['required', 'exists:esim_types,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:active,disabled'],
        ]);

        $esimCode->update($data);

        return redirect()->route('admin.esim-codes.index')->with('success', 'eSIM QR updated.');
    }

    public function destroy(EsimCode $esimCode): RedirectResponse
    {
        $esimCode->delete();

        return redirect()->route('admin.esim-codes.index')->with('success', 'eSIM QR deleted.');
    }
}
