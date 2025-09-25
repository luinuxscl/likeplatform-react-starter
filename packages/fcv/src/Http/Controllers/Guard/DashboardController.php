<?php

namespace Like\Fcv\Http\Controllers\Guard;

use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('fcv/guard/index', [
            'now' => now()->toIso8601String(),
        ]);
    }
}
