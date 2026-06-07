<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! $request->user()?->isAdmin()) {
            return redirect()
                ->route('dashboard')
                ->with('error', 'You must be an admin to access that page.');
        }

        return $next($request);
    }
}
