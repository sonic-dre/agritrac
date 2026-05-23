<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AgentController extends Controller
{
    public function index(): JsonResponse
    {
        $this->requireManager();

        $agents = Agent::withCount(['trips', 'transactions'])
            ->orderBy('name')
            ->get()
            ->map(fn ($a) => [
                'id'             => $a->id,
                'name'           => $a->name,
                'initials'       => $a->initials,
                'region'         => $a->region,
                'base_location'  => $a->base_location,
                'phone'          => $a->phone ?? '—',
                'email'          => $a->email ?? '—',
                'avatar_color'   => $a->avatar_color,
                'is_active'      => $a->is_active,
                'has_login'      => !empty($a->email) && !empty($a->password),
                'trips_count'    => $a->trips_count,
                'txn_count'      => $a->transactions_count,
                'created'        => $a->created_at->format('d M Y'),
            ]);

        $active = $agents->where('is_active', true)->count();

        return response()->json([
            'stats' => [
                'total'       => $agents->count(),
                'active'      => $active,
                'inactive'    => $agents->count() - $active,
                'with_login'  => $agents->where('has_login', true)->count(),
            ],
            'agents' => $agents->values(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requireManager();

        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'region'        => 'required|string|max:100',
            'base_location' => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:30',
            'email'         => 'nullable|email|unique:agents,email',
            'password'      => ['nullable', Password::min(8)],
            'avatar_color'  => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ]);

        // Auto-generate initials from name
        $parts = explode(' ', trim($data['name']));
        $data['initials'] = strtoupper(substr($parts[0], 0, 1)) . strtoupper(substr($parts[1] ?? '', 0, 1));

        // Remove password if blank
        if (empty($data['password'])) {
            unset($data['password']);
        }

        $agent = Agent::create($data);

        return response()->json(['success' => true, 'id' => $agent->id]);
    }

    public function update(Request $request, Agent $agent): JsonResponse
    {
        $this->requireManager();

        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'region'        => 'required|string|max:100',
            'base_location' => 'nullable|string|max:100',
            'phone'         => 'nullable|string|max:30',
            'email'         => 'nullable|email|unique:agents,email,' . $agent->id,
            'password'      => ['nullable', Password::min(8)],
            'avatar_color'  => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ]);

        // Refresh initials if name changed
        $parts = explode(' ', trim($data['name']));
        $data['initials'] = strtoupper(substr($parts[0], 0, 1)) . strtoupper(substr($parts[1] ?? '', 0, 1));

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $agent->update($data);

        return response()->json(['success' => true]);
    }

    public function toggleActive(Agent $agent): JsonResponse
    {
        $this->requireManager();

        $agent->update(['is_active' => !$agent->is_active]);

        // Revoke all tokens if deactivated
        if (!$agent->is_active) {
            $agent->tokens()->delete();
        }

        return response()->json([
            'success'   => true,
            'is_active' => $agent->is_active,
        ]);
    }

    public function destroy(Agent $agent): JsonResponse
    {
        $this->requireManager();

        $agent->tokens()->delete();
        $agent->syncRecords()->delete();
        $agent->transactions()->delete();
        $agent->trips()->delete();
        $agent->delete();

        return response()->json(['success' => true]);
    }

    private function requireManager(): void
    {
        if (!Auth::user()?->isManager()) {
            abort(403, 'Manager access required.');
        }
    }
}
