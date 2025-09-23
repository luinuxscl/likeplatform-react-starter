<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class ChangelogController
{
    public function index()
    {
        $path = base_path('CHANGELOG.md');
        $content = '';
        try {
            if (File::exists($path)) {
                $content = File::get($path);
            }
        } catch (\Throwable $e) {
            $content = '';
        }

        return Inertia::render('changelog/index', [
            'content' => $content,
        ]);
    }
}
