<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'manufacturer'])
            ->active()
            ->inStock();

        if ($request->filled('category')) {
            $query->whereHas('category', fn($q) => $q->where('slug', $request->category));
        }
        if ($request->filled('manufacturer')) {
            $query->whereHas('manufacturer', fn($q) => $q->where('slug', $request->manufacturer));
        }
        if ($request->filled('search')) {
            $query->where(fn($q) => $q
                ->where('name', 'like', "%{$request->search}%")
                ->orWhere('short_description', 'like', "%{$request->search}%")
            );
        }
        if ($request->filled('sort')) {
            match ($request->sort) {
                'price_asc'    => $query->orderBy('price'),
                'price_desc'   => $query->orderByDesc('price'),
                'newest'       => $query->orderByDesc('created_at'),
                'featured'     => $query->orderByDesc('featured'),
                default        => $query->orderBy('sort_order'),
            };
        } else {
            $query->orderBy('sort_order')->orderByDesc('featured');
        }

        $products = $query->paginate($request->get('per_page', 12));

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page'    => $products->lastPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $product = Product::with(['category', 'manufacturer'])
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $product]);
    }
}

// ─────────────────────────────────────────────────────────────────────────────

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->orderBy('sort_order')
            ->withCount(['products' => fn($q) => $q->active()])
            ->get();

        return response()->json(['data' => $categories]);
    }
}
