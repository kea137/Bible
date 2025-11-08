<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        // Get the user's role numbers (they may have multiple roles)
        $roleNumbers = $request->user()
            ? $request->user()->roles()->pluck('role_number')->toArray()
            : [];

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $request->user(),
                'roleNumbers' => $roleNumbers,
            ],
            'theme' => $request->user() ? ($request->user()->appearance_preferences['theme'] ?? 'system') : 'system',
            'language' => $request->user() ? ($request->user()->language ?? 'en') : 'en',
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'success' => fn () => $request->session()->get('success'),
            'error' => fn () => $request->session()->get('error'),
            'info' => fn () => $request->session()->get('info'),
            'csrf_token' => csrf_token(),
        ];
    }
}
