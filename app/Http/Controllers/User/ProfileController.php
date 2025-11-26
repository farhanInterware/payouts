<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        LoggingService::logActivity('User', 'view_profile', [
            'user_id' => $user->id,
        ], auth()->id());
        
        return view('user.profile.show', compact('user'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        LoggingService::logActivity('User', 'change_password', [
            'user_id' => $user->id,
        ], auth()->id());

        return redirect()->route('user.profile.show')
            ->with('success', 'Password updated successfully.');
    }
}

