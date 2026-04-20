<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('tenant_id')) {
            return redirect()->route('login');
        }

        return $next($request);
    }
}
