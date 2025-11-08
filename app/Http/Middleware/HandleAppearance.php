<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class HandleAppearance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get appearance from user's database settings or default to 'system'
        $appearance = 'system';
        $language = 'en';

        if ($request->user()) {
            $appearance = $request->user()->appearance_preferences['theme'] ?? 'system';
            $language = $request->user()->language ?? 'en';
        }

        View::share('appearance', $appearance);
        View::share('language', $language);

        return $next($request);
    }
}
