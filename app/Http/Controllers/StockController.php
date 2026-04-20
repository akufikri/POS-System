<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockLog;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function __construct(private StockService $stockService) {}

    public function index(): View
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        $logs = StockLog::with(['product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('page.stock.index', compact('products', 'logs'));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'type' => 'required|in:in,out',
            'notes' => 'nullable|string|max:255',
        ]);

        try {
            if ($request->type === 'in') {
                $this->stockService->stockIn($request->product_id, $request->quantity, $request->notes);
            } else {
                $this->stockService->stockOut($request->product_id, $request->quantity, $request->notes);
            }

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diperbarui.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui stok: ' . $e->getMessage(),
            ], 400);
        }
    }
}
