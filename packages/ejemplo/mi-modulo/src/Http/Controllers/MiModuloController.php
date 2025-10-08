<?php

namespace Ejemplo\MiModulo\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador principal del módulo
 */
class MiModuloController extends Controller
{
    /**
     * Muestra la página principal del módulo
     */
    public function index(): Response
    {
        return Inertia::render('MiModulo/Index', [
            'title' => 'Mi Módulo',
            'description' => 'Bienvenido al módulo de ejemplo',
            'stats' => [
                'total_items' => 0,
                'active_items' => 0,
                'pending_items' => 0,
            ],
        ]);
    }
}
