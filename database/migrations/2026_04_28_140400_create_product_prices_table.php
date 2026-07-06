<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('type', 20); // retail/wholesale/cost/special
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('UZS');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index('product_id');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};
