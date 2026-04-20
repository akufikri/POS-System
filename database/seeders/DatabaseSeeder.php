<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create demo tenant
        $tenant = Tenant::create([
            'name' => 'Volare Caffe',
            'slug' => 'volare-caffe',
            'primary_color' => '#edcc94',
        ]);

        // Owner account
        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Owner Volare',
            'email' => 'owner@volare.demo',
            'role' => 'owner',
            'password' => Hash::make('password'),
        ]);

        // Cashier accounts
        $cashier1 = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Budi Santoso',
            'email' => 'budi@volare.demo',
            'role' => 'cashier',
            'password' => Hash::make('password'),
        ]);

        $cashier2 = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siti Rahayu',
            'email' => 'siti@volare.demo',
            'role' => 'cashier',
            'password' => Hash::make('password'),
        ]);

        // Sample products
        $products = [
            ['name' => 'Kopi Susu',          'price' => 25000, 'cost' => 8000,  'description' => 'Kopi susu creamy dengan espresso'],
            ['name' => 'Americano',           'price' => 20000, 'cost' => 5000,  'description' => 'Espresso + air panas'],
            ['name' => 'Matcha Latte',        'price' => 28000, 'cost' => 10000, 'description' => 'Matcha premium dengan susu segar'],
            ['name' => 'Croissant',           'price' => 22000, 'cost' => 9000,  'description' => 'Croissant butter Prancis'],
            ['name' => 'Es Kopi Susu',        'price' => 27000, 'cost' => 9000,  'description' => 'Kopi susu dingin dengan es batu'],
            ['name' => 'Teh Tarik',           'price' => 18000, 'cost' => 4000,  'description' => 'Teh tarik khas Malaysia'],
            ['name' => 'Chocolate Cake',      'price' => 35000, 'cost' => 14000, 'description' => 'Slice kue coklat lembab'],
            ['name' => 'Mineral Water',       'price' => 8000,  'cost' => 2000,  'description' => 'Air mineral 600ml'],
        ];

        $productModels = collect($products)->map(fn($p) => Product::create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
            ...$p,
        ]));

        // Sample orders + shifts (last 7 days)
        // Each cashier opens one shift per day; last day's shifts stay open
        $cashiers = collect([$cashier1, $cashier2]);

        for ($day = 6; $day >= 0; $day--) {
            $date = now()->subDays($day);
            $isToday = $day === 0;

            // Create one shift per cashier per day
            $shifts = $cashiers->map(function ($cashier) use ($tenant, $date) {
                return Shift::create([
                    'tenant_id'    => $tenant->id,
                    'user_id'      => $cashier->id,
                    'opened_at'    => $date->copy()->setTime(8, 0),
                    'opening_cash' => 500000,
                ]);
            });

            $ordersPerDay = rand(5, 15);

            for ($i = 0; $i < $ordersPerDay; $i++) {
                $cashier = $cashiers->random();
                $shift = $shifts->firstWhere('user_id', $cashier->id);
                $selectedProducts = $productModels->random(rand(1, 3));

                $totalAmount = 0;
                $totalCost = 0;
                $items = [];

                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $subtotal = $product->price * $qty;
                    $totalAmount += $subtotal;
                    $totalCost += $product->cost * $qty;
                    $items[] = [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'unit_price'   => $product->price,
                        'unit_cost'    => $product->cost,
                        'quantity'     => $qty,
                        'subtotal'     => $subtotal,
                        'created_at'   => $date,
                        'updated_at'   => $date,
                    ];
                }

                $order = Order::create([
                    'tenant_id'      => $tenant->id,
                    'user_id'        => $cashier->id,
                    'shift_id'       => $shift->id,
                    'total_amount'   => $totalAmount,
                    'total_cost'     => $totalCost,
                    'discount_value' => 0,
                    'payment_status' => 'paid',
                    'created_at'     => $date,
                    'updated_at'     => $date,
                ]);

                foreach ($items as $item) {
                    $order->items()->create($item);
                }

                Payment::create([
                    'order_id'   => $order->id,
                    'method'     => 'cash',
                    'amount'     => $totalAmount,
                    'status'     => 'paid',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            // Close shifts for past days
            if (! $isToday) {
                $shifts->each(function ($shift) use ($date) {
                    $cashPayments = Payment::join('orders', 'payments.order_id', '=', 'orders.id')
                        ->where('orders.shift_id', $shift->id)
                        ->where('payments.method', 'cash')
                        ->where('payments.status', 'paid')
                        ->sum('payments.amount');

                    $expectedCash = $shift->opening_cash + $cashPayments;

                    $shift->update([
                        'closed_at'     => $date->copy()->setTime(17, 0),
                        'closing_cash'  => $expectedCash + rand(-10000, 10000),
                        'expected_cash' => $expectedCash,
                    ]);
                });
            }
        }
    }
}
