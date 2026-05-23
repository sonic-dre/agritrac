<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(): JsonResponse
    {
        $this->requireAdmin();

        $users = User::orderBy('name')->get()->map(fn ($u) => [
            'id'         => $u->id,
            'name'       => $u->name,
            'email'      => $u->email,
            'role'       => $u->role,
            'role_label' => $u->roleLabel(),
            'initials'   => $this->initials($u->name),
            'created'    => $u->created_at->format('d M Y'),
            'is_self'    => $u->id === Auth::id(),
        ]);

        return response()->json([
            'stats' => [
                'total'   => User::count(),
                'admins'  => User::where('role', 'admin')->count(),
                'managers'=> User::where('role', 'manager')->count(),
                'viewers' => User::where('role', 'viewer')->count(),
            ],
            'users' => $users,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->requireAdmin();

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email',
            'role'     => 'required|in:admin,manager,viewer',
            'password' => ['required', Password::min(8)],
        ]);

        User::create($data);

        return response()->json(['success' => true]);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->requireAdmin();

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'role'     => 'required|in:admin,manager,viewer',
            'password' => ['nullable', Password::min(8)],
        ]);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        // Prevent removing admin role from yourself
        if ($user->id === Auth::id() && $data['role'] !== 'admin') {
            return response()->json(['errors' => ['role' => 'You cannot demote yourself.']], 422);
        }

        $user->update($data);

        return response()->json(['success' => true]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->requireAdmin();

        if ($user->id === Auth::id()) {
            return response()->json(['errors' => ['id' => 'You cannot delete your own account.']], 422);
        }

        $user->delete();

        return response()->json(['success' => true]);
    }

    private function requireAdmin(): void
    {
        if (!Auth::user()?->isAdmin()) {
            abort(403, 'Admin access required.');
        }
    }

    private function initials(string $name): string
    {
        $parts = explode(' ', trim($name));
        $first = strtoupper(substr($parts[0], 0, 1));
        $last  = isset($parts[1]) ? strtoupper(substr($parts[1], 0, 1)) : '';
        return $first . $last;
    }
}
