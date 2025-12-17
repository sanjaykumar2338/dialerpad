<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CallCard;
use App\Services\QrCodeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CallCardController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodeService
    ) {
    }

    public function index(): View
    {
        $cards = CallCard::with('creator')
            ->latest()
            ->paginate(10);

        return view('admin.call-cards.index', compact('cards'));
    }

    public function create(): View
    {
        return view('admin.call-cards.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_prefix' => ['nullable', 'string', 'max:255'],
            'prefix' => ['required', 'string', 'max:50'],
            'total_minutes' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $quantity = (int) $data['quantity'];
        $baseName = $quantity > 1 && !empty($data['name_prefix'])
            ? $data['name_prefix']
            : $data['name'];

        if ($quantity === 1) {
            $card = CallCard::create([
                'name' => $data['name'],
                'prefix' => $data['prefix'],
                'total_minutes' => $data['total_minutes'],
                'notes' => $data['notes'] ?? null,
                'uuid' => (string) Str::uuid(),
                'created_by' => Auth::id(),
            ]);

            $this->qrCodeService->generateForCallCard($card);

            return redirect()
                ->route('admin.call-cards.index')
                ->with('status', 'Call card created successfully.');
        }

        DB::transaction(function () use ($quantity, $baseName, $data): void {
            for ($i = 1; $i <= $quantity; $i++) {
                $card = CallCard::create([
                    'name' => $baseName . ' #' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'prefix' => $data['prefix'],
                    'total_minutes' => $data['total_minutes'],
                    'notes' => $data['notes'] ?? null,
                    'uuid' => (string) Str::uuid(),
                    'created_by' => Auth::id(),
                ]);

                $this->qrCodeService->generateForCallCard($card);
            }
        });

        return redirect()
            ->route('admin.call-cards.index')
            ->with('status', 'Created ' . $quantity . ' cards successfully.');
    }

    public function show(CallCard $call_card): View
    {
        $call_card->load([
            'sessions' => function ($query) {
                $query->latest();
            },
        ]);

        return view('admin.call-cards.show', ['card' => $call_card]);
    }

    public function edit(CallCard $call_card): View
    {
        return view('admin.call-cards.edit', ['card' => $call_card]);
    }

    public function update(Request $request, CallCard $call_card): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'prefix' => ['required', 'string', 'max:50'],
            'total_minutes' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $call_card->update($data);
        $this->qrCodeService->generateForCallCard($call_card);

        return redirect()
            ->route('admin.call-cards.index')
            ->with('status', 'Call card updated successfully.');
    }

    public function destroy(CallCard $call_card): RedirectResponse
    {
        $call_card->delete();

        return redirect()
            ->route('admin.call-cards.index')
            ->with('status', 'Call card deleted successfully.');
    }
}
