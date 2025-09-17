<?php

namespace App\Http\Controllers\I18n;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;

class LanguageController extends Controller
{
    /**
     * Persist selected locale in session/cookie.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'locale' => ['required', 'string', 'in:en,es'],
        ]);

        $locale = $request->string('locale')->toString();

        session(['locale' => $locale]);
        cookie()->queue(cookie('locale', $locale, 60 * 24 * 365)); // 1 año

        return back(303);
    }

    /**
     * Serve locale JSON translations with caching headers.
     */
    public function json(string $locale): Response
    {
        abort_unless(in_array($locale, ['en', 'es'], true), 404);

        $path = lang_path("{$locale}.json");
        if (! File::exists($path)) {
            return response('{}', 200, [
                'Content-Type' => 'application/json; charset=utf-8',
                'Cache-Control' => 'public, max-age=3600',
            ]);
        }

        $content = File::get($path);

        return response($content, 200, [
            'Content-Type' => 'application/json; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
