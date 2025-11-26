<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\LoggingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user();
        
        LoggingService::logActivity('Admin', 'view_profile', [
            'user_id' => $user->id,
        ], auth()->id());
        
        return view('admin.profile.show', compact('user'));
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

        LoggingService::logActivity('Admin', 'change_own_password', [
            'user_id' => $user->id,
        ], auth()->id());

        return redirect()->route('admin.profile.show')
            ->with('success', 'Password updated successfully.');
    }

    public function changeUserPassword(Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::findOrFail($id);
        $user->password = Hash::make($request->password);
        $user->save();

        LoggingService::logActivity('Admin', 'change_user_password', [
            'target_user_id' => $user->id,
            'target_user_email' => $user->email,
        ], auth()->id());

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'User password updated successfully.');
    }
}

