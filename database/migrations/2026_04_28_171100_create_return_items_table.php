<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('return_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained('product_returns')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('name', 255); // snapshot of product name
            $table->string('sku', 100)->nullable();
            $table->decimal('quantity', 10, 3)->default(1);
            $table->foreignId('serial_id')->nullable()->constrained('product_serials')->nullOnDelete();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('return_id');
            $table->index('serial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('return_items');
    }
};
