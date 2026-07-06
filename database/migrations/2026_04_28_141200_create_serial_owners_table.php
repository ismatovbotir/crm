<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_owners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serial_id')->constrained('product_serials')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->unsignedBigInteger('sell_item_id')->nullable();   // no FK — sell_items created later
            $table->unsignedBigInteger('return_item_id')->nullable(); // no FK — return_items created later
            $table->timestamp('acquired_at');
            $table->timestamp('released_at')->nullable(); // null = current owner
            $table->timestamps();
            $table->index('serial_id');
            $table->index('customer_id');
            $table->index('released_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_owners');
    }
};
