<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
        private ShiftService $shiftService
    ) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $userId = auth()->id();
        $shift = $this->shiftService->getCurrentShift($userId);

        if (!$shift) {
            return response()->json([
                'success' => false,
                'message' => 'Anda harus membuka shift terlebih dahulu sebelum melakukan transaksi.',
            ], 400);
        }

        try {
            $order = $this->orderService->createOrder([
                'tenant_id' => session('tenant_id'),
                'user_id' => $userId,
                'shift_id' => $shift->id,
                'items' => $request->items,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil.',
                'order_id' => $order->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Order creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses transaksi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
