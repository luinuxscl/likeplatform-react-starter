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

        $options = app(\App\Services\Options::class);

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(),
                'roles' => $request->user()?->getRoleNames()?->toArray(),
                'permissions' => $request->user()?->getAllPermissions()?->pluck('name')?->toArray(),
            ],
            'app' => [
                'name' => $options->get('app.name', config('app.name')),
                'description' => $options->get('app.description'),
                'logo_url' => $options->get('app.logo_url'),
                'date_format' => $options->get('app.date_format', 'dd/mm/YYYY'),
                'timezone' => $options->get('app.timezone', config('app.timezone')),
            ],
            'flash' => [
                'success' => fn () => session('success'),
                'error' => fn () => session('error'),
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'expansion' => [
                'themes' => [
                    'current' => session('expansion.theme', config('expansion.themes.default_theme')),
                    'available' => config('expansion.themes.available_themes'),
                    'default' => config('expansion.themes.default_theme'),
                ],
            ],
            'i18n' => [
                'locale' => app()->getLocale(),
                'available' => ['en', 'es'],
            ],
        ];
    }
}
