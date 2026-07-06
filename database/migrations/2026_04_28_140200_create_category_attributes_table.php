<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('type', 20)->default('text'); // text/number/boolean/select
            $table->string('unit', 50)->nullable();
            $table->json('options')->nullable(); // for select type
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(false);
            $table->timestamps();
            $table->index('category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_attributes');
    }
};
