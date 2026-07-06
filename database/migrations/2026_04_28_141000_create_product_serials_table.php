<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('serial_number', 100);
            $table->boolean('is_external')->default(false);
            $table->string('ext_brand', 100)->nullable();  // brand for external equipment
            $table->string('ext_model', 100)->nullable();  // model for external equipment
            $table->string('current_status', 20)->default('available'); // available/sold/returned/in_repair
            $table->foreignId('current_owner_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'serial_number']);
            $table->index('current_status');
            $table->index('current_owner_id');
            $table->index('is_external');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_serials');
    }
};
