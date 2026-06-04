<?php

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Account deactivated.']);
        }

        if (!empty($roles) && !in_array($user->role, $roles)) {
            ActivityLog::create([
                'user_id' => $user->id,
                'action_type' => 'access_denied',
                'description' => 'Attempted to access: ' . $request->path(),
                'ip_address' => $request->ip(),
            ]);
            abort(403, 'Access Denied');
        }

        return $next($request);
    }
}
