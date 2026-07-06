<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->string('sku', 100)->unique();
            $table->string('name_ru', 255);
            $table->string('name_uz', 255)->nullable();
            $table->text('description_ru')->nullable();
            $table->text('description_uz')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model_number', 100)->nullable();
            $table->string('unit', 50)->default('шт');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_visible_portal')->default(true);
            $table->boolean('is_serial')->default(false);
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('category_id');
            $table->index('is_active');
            $table->index('is_visible_portal');
            $table->index('is_serial');
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
