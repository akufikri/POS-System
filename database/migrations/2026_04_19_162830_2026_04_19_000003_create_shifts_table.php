<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->unsignedBigInteger('opening_cash')->default(0);
            $table->unsignedBigInteger('closing_cash')->nullable();
            $table->unsignedBigInteger('expected_cash')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index('opened_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
