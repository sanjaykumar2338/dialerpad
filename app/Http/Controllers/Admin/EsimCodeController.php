<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EsimCode;
use App\Models\EsimType;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $types = EsimType::where('status', 'active')->orderBy('name')->get();

        return view('admin.esim.codes.create', compact('types'));
    }

    public function store(Request $request, QrCodeService $qr): RedirectResponse
    {
        $data = $request->validate([
            'esim_type_id' => ['required', 'exists:esim_types,id'],
            'label' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $type = EsimType::findOrFail($data['esim_type_id']);
        $quantity = (int) $data['quantity'];

        if (empty($type->product_id)) {
            return back()
                ->withInput()
                ->withErrors('Selected plan is missing a Mobimatter product ID.');
        }

        DB::transaction(function () use ($quantity, $type, $data, $qr): void {
            for ($i = 1; $i <= $quantity; $i++) {
                $code = EsimCode::create([
                    'uuid' => (string) Str::uuid(),
                    'esim_type_id' => $type->id,
                    'product_id' => $type->product_id,
                    'label' => $data['label'] ?? null,
                    'status' => 'unused',
                ]);

                $url = url('/esim/' . $code->uuid);
                $qr->generateForEsimCode($code, $url);
            }
        });

        return redirect()->route('admin.esim-codes.index')->with('success', "eSIM QR created successfully ({$quantity})");
    }

    public function edit(EsimCode $esimCode): View
    {
        $types = EsimType::where('status', 'active')
            ->orWhere('id', $esimCode->esim_type_id)
            ->orderBy('name')
            ->get();

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
            'status' => ['required', 'in:unused,used,disabled'],
        ]);

        $type = EsimType::findOrFail($data['esim_type_id']);

        if ($esimCode->status === 'used' && $data['status'] !== 'used') {
            return back()
                ->withInput()
                ->withErrors('Used codes cannot be reactivated.');
        }

        $esimCode->update([
            ...$data,
            'product_id' => $type->product_id,
        ]);

        return redirect()->route('admin.esim-codes.index')->with('success', 'eSIM QR updated.');
    }

    public function destroy(EsimCode $esimCode): RedirectResponse
    {
        $esimCode->delete();

        return redirect()->route('admin.esim-codes.index')->with('success', 'eSIM QR deleted.');
    }
}
