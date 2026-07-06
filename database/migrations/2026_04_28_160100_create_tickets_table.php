<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('ticket_categories')->nullOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('serial_id')->nullable()->constrained('product_serials')->nullOnDelete();
            $table->string('priority', 20)->default('medium'); // low/medium/high/critical
            $table->string('status', 30)->default('open'); // open/in_progress/pending_customer/resolved/closed
            $table->string('subject', 500);
            $table->text('description')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->unsignedTinyInteger('csat_score')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('customer_id');
            $table->index('assignee_id');
            $table->index('status');
            $table->index('priority');
            $table->index('created_by');
            $table->index('serial_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
