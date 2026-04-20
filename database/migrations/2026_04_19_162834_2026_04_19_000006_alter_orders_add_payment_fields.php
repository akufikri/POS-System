<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('discount_type', ['nominal', 'percent'])->nullable();
            $table->unsignedBigInteger('discount_value')->default(0);
            $table->unsignedBigInteger('net_amount')->storedAs('total_amount - discount_value');
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->foreignId('shift_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['discount_type', 'discount_value', 'net_amount', 'payment_status']);
            $table->dropForeign(['shift_id']);
            $table->dropColumn('shift_id');
        });
    }
};
