<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboarding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and hasn't completed onboarding
        if ($request->user() && !$request->user()->onboarding_completed) {
            // Don't redirect if already on onboarding page or logout
            if (!$request->is('onboarding') && !$request->is('onboarding/*') && !$request->is('logout')) {
                return redirect()->route('onboarding');
            }
        }

        return $next($request);
    }
}
