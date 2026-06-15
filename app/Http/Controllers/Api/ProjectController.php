<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Project::active()->orderBy('sort_order')->orderBy('id', 'desc');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhere('short_description', 'like', "%{$request->search}%");
            });
        }

        $projects = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'data' => $projects->items(),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page'    => $projects->lastPage(),
                'per_page'     => $projects->perPage(),
                'total'        => $projects->total(),
            ],
        ]);
    }

    public function featured(): JsonResponse
    {
        $projects = Project::active()->featured()
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return response()->json(['data' => $projects]);
    }

    public function show(string $slug): JsonResponse
    {
        $project = Project::active()->where('slug', $slug)->firstOrFail();
        return response()->json(['data' => $project]);
    }
}
