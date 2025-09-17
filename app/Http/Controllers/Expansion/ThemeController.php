<?php

namespace App\Http\Controllers\Expansion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class ThemeController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $available = array_keys(config('expansion.themes.available_themes', []));

        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in($available)],
        ]);

        $theme = $validated['theme'];

        Session::put('expansion.theme', $theme);

        return back()->with('status', 'theme-updated');
    }
}
