<?php

namespace Ejemplo\MiModulo\Http\Controllers;

use App\Http\Controllers\Controller;
use Ejemplo\MiModulo\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador para gestión de items
 */
class ItemsController extends Controller
{
    /**
     * Muestra el listado de items
     */
    public function index(): Response
    {
        $items = Item::query()
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('MiModulo/Items/Index', [
            'items' => $items,
        ]);
    }

    /**
     * Crea un nuevo item
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive,pending',
        ]);

        Item::create($validated);

        return redirect()
            ->route('mi-modulo.items.index')
            ->with('success', 'Item creado exitosamente');
    }
}
