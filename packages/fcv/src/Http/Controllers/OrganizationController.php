<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Like\Fcv\Http\Requests\OrganizationRequest;
use Like\Fcv\Models\Organization;

class OrganizationController extends Controller
{
    public function index(): Response
    {
        $organizations = Organization::query()
            ->withTrashed()
            ->withCount(['courses', 'memberships', 'persons'])
            ->orderBy('name')
            ->get()
            ->map(function (Organization $organization) {
                return [
                    'id' => $organization->id,
                    'name' => $organization->name,
                    'acronym' => $organization->acronym,
                    'type' => $organization->type,
                    'description' => $organization->description,
                    'courses_count' => $organization->courses_count,
                    'memberships_count' => $organization->memberships_count,
                    'persons_count' => $organization->persons_count,
                    'deleted_at' => $organization->deleted_at?->toIso8601String(),
                    'can_hard_delete' => ($organization->courses_count + $organization->memberships_count) === 0,
                    'can_restore' => $organization->trashed(),
                ];
            });

        $types = collect([
            'interna' => 'Interna',
            'convenio' => 'Convenio',
            'externa' => 'Externa',
        ])->map(function ($label, $value) {
            return [
                'value' => $value,
                'label' => $label,
            ];
        })->values();

        return Inertia::render('fcv/organizations/index', [
            'organizations' => $organizations,
            'types' => $types,
        ]);
    }

    public function store(OrganizationRequest $request): RedirectResponse
    {
        Organization::query()->create($request->validated());

        return redirect()->route('fcv.organizations.index')->with('flash.success', 'Organización creada correctamente.');
    }

    public function update(OrganizationRequest $request, Organization $organization): RedirectResponse
    {
        $organization->update($request->validated());

        return redirect()->route('fcv.organizations.index')->with('flash.success', 'Organización actualizada correctamente.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        $hasRelations = $organization->courses()->exists() || $organization->memberships()->exists();

        if ($hasRelations) {
            $organization->delete();

            return redirect()->route('fcv.organizations.index')->with('flash.success', 'Organización archivada (tiene relaciones activas).');
        }

        $organization->forceDelete();

        return redirect()->route('fcv.organizations.index')->with('flash.success', 'Organización eliminada permanentemente.');
    }

    public function restore(int $organizationId): RedirectResponse
    {
        $organization = Organization::withTrashed()->findOrFail($organizationId);

        $organization->restore();

        return redirect()->route('fcv.organizations.index')->with('flash.success', 'Organización restaurada correctamente.');
    }
}
