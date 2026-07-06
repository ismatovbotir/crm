<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serial_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serial_id')->constrained('product_serials')->cascadeOnDelete();
            $table->string('status', 20); // available/sold/returned/in_repair
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('serial_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serial_statuses');
    }
};
