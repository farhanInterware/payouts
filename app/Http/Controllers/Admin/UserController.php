<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        LoggingService::logActivity('Admin', 'view_users', [
            'filters' => $request->only(['search']),
        ], auth()->id());

        $query = User::where('role', 'user');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->withCount('transactions')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'user',
        ]);

        LoggingService::logActivity('Admin', 'create_user', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_name' => $user->name,
        ], auth()->id());

        return redirect()->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function show($id)
    {
        $user = User::with('transactions')->findOrFail($id);
        
        LoggingService::logActivity('Admin', 'view_user', [
            'user_id' => $user->id,
            'user_email' => $user->email,
        ], auth()->id());
        
        return view('admin.users.show', compact('user'));
    }
}

