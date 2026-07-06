<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sell_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sell_id')->constrained('sells')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            $table->decimal('quantity', 10, 3)->default(1);
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->string('notes', 500)->nullable();

            $table->timestamps();

            $table->index('sell_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sell_items');
    }
};
