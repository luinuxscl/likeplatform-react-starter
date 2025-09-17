<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supported = ['en', 'es'];

        $locale = session('locale')
            ?? $request->cookie('locale')
            ?? $request->query('lang')
            ?? substr((string) $request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2)
            ?? config('app.locale');

        $locale = in_array($locale, $supported, true) ? $locale : config('app.locale');

        app()->setLocale($locale);

        // Share for views (Blade) if needed
        view()->share('app_locale', $locale);

        return $next($request);
    }
}
