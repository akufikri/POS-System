<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockLog;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function adjustStock(int $productId, int $quantity, string $type, ?string $referenceId = null, ?string $notes = null): Product
    {
        return DB::transaction(function () use ($productId, $quantity, $type, $referenceId, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            
            // Adjust current stock
            $product->stock += $quantity;
            $product->save();

            // Log the change
            StockLog::create([
                'tenant_id' => session('tenant_id'),
                'product_id' => $productId,
                'user_id' => auth()->id(),
                'quantity' => $quantity,
                'type' => $type,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);

            return $product;
        });
    }

    public function stockIn(int $productId, int $quantity, ?string $notes = null): Product
    {
        return $this->adjustStock($productId, abs($quantity), 'in', null, $notes);
    }

    public function stockOut(int $productId, int $quantity, ?string $notes = null): Product
    {
        return $this->adjustStock($productId, -abs($quantity), 'out', null, $notes);
    }

    public function logSale(int $productId, int $quantity, string $orderId): Product
    {
        return $this->adjustStock($productId, -abs($quantity), 'sale', $orderId);
    }
}
