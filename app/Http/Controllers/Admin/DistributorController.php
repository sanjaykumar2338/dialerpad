<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DistributorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'status' => $request->query('status'),
            'search' => $request->query('search'),
        ];

        $query = User::query()
            ->where('is_admin', false)
            ->whereIn('role', User::DISTRIBUTOR_ROLES)
            ->latest();

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

        if ($filters['search']) {
            $term = '%'.$filters['search'].'%';
            $query->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term)
                    ->orWhere('company_name', 'like', $term);
            });
        }

        return view('admin.distributors.index', [
            'distributors' => $query->paginate(12)->withQueryString(),
            'filters' => $filters,
            'statuses' => User::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('admin.distributors.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'status' => $data['status'],
            'role' => $data['role'],
            'password' => $data['password'],
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        return redirect()
            ->route('admin.distributors.index')
            ->with('status', 'Distributor created successfully.');
    }

    public function edit(User $distributor): View
    {
        $this->ensureDistributor($distributor);

        return view('admin.distributors.edit', [
            'distributor' => $distributor,
        ]);
    }

    public function update(Request $request, User $distributor): RedirectResponse
    {
        $this->ensureDistributor($distributor);

        $data = $this->validatedData($request, $distributor);

        $distributor->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'company_name' => $data['company_name'] ?? null,
            'status' => $data['status'],
            'role' => $data['role'],
        ]);

        if (! empty($data['password'])) {
            $distributor->password = $data['password'];
        }

        $distributor->save();

        return redirect()
            ->route('admin.distributors.index')
            ->with('status', 'Distributor updated successfully.');
    }

    public function updateStatus(Request $request, User $distributor): RedirectResponse
    {
        $this->ensureDistributor($distributor);

        $data = $request->validate([
            'status' => ['required', Rule::in(User::STATUSES)],
        ]);

        $distributor->update(['status' => $data['status']]);

        return back()->with('status', 'Distributor status updated.');
    }

    public function destroy(User $distributor): RedirectResponse
    {
        $this->ensureDistributor($distributor);

        if ($this->hasOwnedData($distributor)) {
            $distributor->update(['status' => User::STATUS_INACTIVE]);

            return redirect()
                ->route('admin.distributors.index')
                ->with('status', 'Distributor has account data, so it was disabled instead of deleted.');
        }

        $distributor->delete();

        return redirect()
            ->route('admin.distributors.index')
            ->with('status', 'Distributor deleted successfully.');
    }

    private function validatedData(Request $request, ?User $distributor = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($distributor?->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::in(User::STATUSES)],
            'role' => ['required', Rule::in(User::DISTRIBUTOR_ROLES)],
            'password' => [$distributor ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function ensureDistributor(User $user): void
    {
        abort_unless(! $user->is_admin && in_array($user->role, User::DISTRIBUTOR_ROLES, true), 404);
    }

    private function hasOwnedData(User $distributor): bool
    {
        return $distributor->batchRequests()->exists()
            || $distributor->batches()->exists()
            || $distributor->callCards()->exists()
            || $distributor->esimCodes()->exists()
            || ActivityLog::where('account_id', $distributor->id)->orWhere('actor_id', $distributor->id)->exists();
    }
}
