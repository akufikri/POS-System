<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        if (!auth()->user()->isOwner()) {
            abort(403);
        }

        $query = Category::query();

        if ($request->filled('search')) {
            $query->where('name', 'LIKE', "%{$request->search}%");
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->get();

        return view('page.categories.index', compact('categories'));
    }

    public function store(Request $request): JsonResponse
    {
        if (!auth()->user()->isOwner()) {
            return response()->json(['success' => false], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category = Category::create([
            'tenant_id' => session('tenant_id'),
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        if (!auth()->user()->isOwner()) {
            return response()->json(['success' => false], 403);
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update([
            'name' => $request->name,
            'sort_order' => $request->sort_order ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'category' => $category,
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        if (!auth()->user()->isOwner()) {
            return response()->json(['success' => false], 403);
        }

        if ($category->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete category with products',
            ], 400);
        }

        $category->delete();

        return response()->json(['success' => true]);
    }
}
