<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::where('tenant_id', session('tenant_id'))
            ->where('role', 'cashier');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            });
        }

        $employees = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('page.employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        User::create([
            'tenant_id' => session('tenant_id'),
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Model uses hashed cast
            'role' => 'cashier',
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Employee created successfully']);
    }

    public function update(Request $request, User $user)
    {
        if ($user->tenant_id !== session('tenant_id')) abort(403);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = $request->password;
        }

        $user->update($data);

        return response()->json(['message' => 'Employee updated successfully']);
    }

    public function destroy(User $user)
    {
        if ($user->tenant_id !== session('tenant_id')) abort(403);
        
        $user->delete();

        return response()->json(['message' => 'Employee deleted successfully']);
    }

    public function suspend(Request $request, User $user)
    {
        if ($user->tenant_id !== session('tenant_id')) abort(403);

        $request->validate([
            'duration' => 'required|integer|min:0', // 0 for indefinite/manual toggle
            'reason' => 'required|string|max:500',
        ]);

        $duration = $request->integer('duration');

        if ($duration > 0) {
            $user->update([
                'is_active' => true, // Still active but timestamp will block
                'suspended_until' => now()->addMinutes($duration),
                'suspension_reason' => $request->reason,
            ]);
        } else {
            // Indefinite/Deactivate
            $user->update([
                'is_active' => false,
                'suspended_until' => null,
                'suspension_reason' => $request->reason,
            ]);
        }

        return response()->json(['message' => 'Employee status updated']);
    }
}
