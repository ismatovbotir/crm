<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject', 500);
            $table->text('description')->nullable();
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('needed_by')->nullable();
            $table->string('status', 30)->default('submitted'); // submitted/under_review/quoted/closed
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('customer_id');
            $table->index('manager_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_requests');
    }
};
