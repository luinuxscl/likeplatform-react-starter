<?php

namespace Like\Fcv\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Like\Fcv\Models\Membership;
use Like\Fcv\Models\Person;

class SearchController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $data = $request->validate([
            'q' => ['required', 'string', 'max:100'],
        ]);

        $q = trim($data['q']);
        $normRut = strtolower(preg_replace('/[^0-9kK]/', '', $q));

        $query = Person::query()
            ->with(['memberships.organization', 'courses:fcv_courses.id,name'])
            ->when($q !== '', function ($qb) use ($q, $normRut) {
                $qb->where(function ($w) use ($q, $normRut) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('rut', 'like', "%{$normRut}%");
                });
            })
            ->limit(10);

        $people = $query->get()->map(function (Person $p) {
            return [
                'id' => $p->id,
                'name' => $p->name,
                'rut' => $p->rut,
                'status' => $p->status,
                'memberships' => $p->memberships->map(function (Membership $m) {
                    return [
                        'role' => $m->role,
                        'organization' => [
                            'id' => $m->organization->id,
                            'name' => $m->organization->name,
                            'acronym' => $m->organization->acronym,
                        ],
                    ];
                })->values(),
                'courses' => $p->courses->pluck('name')->values(),
            ];
        });

        return response()->json([
            'results' => $people,
        ]);
    }
}
