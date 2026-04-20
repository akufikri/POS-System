<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspended
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isSuspended()) {
            $user = auth()->user();
            $reason = $user->suspension_reason ?? 'No reason provided.';
            $until = $user->suspended_until ? ' until ' . $user->suspended_until->format('d M Y, H:i') : '';

            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => "Your account has been suspended. Reason: {$reason}{$until}",
            ]);
        }

        return $next($request);
    }
}
