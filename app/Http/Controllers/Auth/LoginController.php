<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return redirect()->route('dashboard');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Account locked check
        if ($user && $user->locked_at) {
            return back()->withErrors(['email' => 'This account is locked. Contact an administrator.']);
        }

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Increment failed attempts
            if ($user) {
                $user->increment('failed_login_attempts');
                if ($user->failed_login_attempts >= 5) {
                    $user->update(['locked_at' => now()]);
                }
            }
            return back()->withErrors(['email' => 'Invalid credentials.'])->withInput(['email' => $request->email]);
        }

        if (!$user->is_active) {
            return back()->withErrors(['email' => 'This account has been deactivated.']);
        }

        // Reset failed attempts on success
        $user->update(['failed_login_attempts' => 0]);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        ActivityLog::create([
            'user_id' => $user->id,
            'action_type' => 'login',
            'description' => 'User logged in',
            'ip_address' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        $userId = Auth::id();
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            ActivityLog::create([
                'user_id' => $userId,
                'action_type' => 'logout',
                'description' => 'User logged out',
            ]);
        }

        return redirect()->route('login');
    }
}
