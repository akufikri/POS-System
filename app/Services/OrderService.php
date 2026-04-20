<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Payment;
use App\Services\StockService;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(private StockService $stockService) {}

    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = 0;
            $totalCost = 0;
            $items = $data['items'];

            $order = Order::create([
                'tenant_id' => $data['tenant_id'],
                'user_id' => $data['user_id'],
                'shift_id' => $data['shift_id'],
                'total_amount' => 0, // Will update later
                'total_cost' => 0,   // Will update later
                'notes' => $data['notes'] ?? null,
                'payment_status' => 'paid',
            ]);

            foreach ($items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $subtotal = $product->price * $item['quantity'];
                
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'unit_cost' => $product->cost,
                    'quantity' => $item['quantity'],
                    'subtotal' => $subtotal,
                ]);

                // Adjust Stock
                $this->stockService->logSale($product->id, $item['quantity'], $order->id);

                $totalAmount += $subtotal;
                $totalCost += ($product->cost * $item['quantity']);
            }

            $order->update([
                'total_amount' => $totalAmount,
                'total_cost' => $totalCost,
            ]);

            // Create Payment Record
            Payment::create([
                'order_id' => $order->id,
                'method' => 'cash', // Default for now
                'amount' => $totalAmount,
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            return $order;
        });
    }
}
