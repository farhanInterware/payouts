<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            LoggingService::logActivity('Auth', 'login_success', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $user->role,
            ], $user->id);
            
            // Redirect based on role
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }
            
            return redirect()->intended(route('user.dashboard'));
        }

        LoggingService::logActivity('Auth', 'login_failed', [
            'email' => $request->email,
        ], null);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $userId = auth()->id();
        
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        if ($userId) {
            LoggingService::logActivity('Auth', 'logout', [
                'user_id' => $userId,
            ], $userId);
        }
        
        return redirect()->route('login');
    }
}

