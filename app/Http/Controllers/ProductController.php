<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private ImageService $imageService) {}

    public function index(Request $request): View
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $perPage = $request->integer('per_page', 10);
        $products = $query->orderBy('name')->paginate($perPage)->withQueryString();
        $categories = Category::orderBy('sort_order')->get();

        return view('page.products.index', compact('products', 'categories'));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = session('tenant_id');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->imageService->upload($request->file('image'));
        }

        unset($data['image']);

        $product = Product::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan.',
            'product' => $this->formatProduct($product),
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            if ($product->image_url) {
                $this->imageService->delete($product->image_url);
            }
            $data['image_url'] = $this->imageService->upload($request->file('image'));
        }

        unset($data['image']);

        $product->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diperbarui.',
            'product' => $this->formatProduct($product->fresh()),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        if ($product->image_url) {
            $this->imageService->delete($product->image_url);
        }

        $product->delete();

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil dihapus.',
        ]);
    }

    private function formatProduct(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'price_formatted' => $product->price_formatted,
            'cost' => $product->cost,
            'cost_formatted' => $product->cost_formatted,
            'category_id' => $product->category_id,
            'category_name' => $product->category?->name,
            'image_url' => $product->image_url,
            'is_active' => $product->is_active,
            'stock' => $product->stock,
        ];
    }
}
