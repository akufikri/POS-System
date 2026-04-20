<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity'); // Positive for Stock In, Negative for Stock Out/Sale
            $table->enum('type', ['in', 'out', 'sale', 'adjustment']);
            $table->string('reference_id')->nullable(); // Order ID or other ref
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('product_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_logs');
    }
};
