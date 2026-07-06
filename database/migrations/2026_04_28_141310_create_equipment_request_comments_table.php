<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_request_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_request_id')->constrained('equipment_requests')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->text('body');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();
            $table->index('equipment_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_request_comments');
    }
};
