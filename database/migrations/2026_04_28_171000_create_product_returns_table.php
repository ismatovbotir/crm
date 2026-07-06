<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_returns', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique(); // RET-0001
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('sell_id')->nullable()->constrained('sells')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('ticket_id')->nullable()->constrained('tickets')->nullOnDelete();
            $table->string('reason', 30); // warranty/defect/changed_mind/other
            $table->string('status', 20)->default('draft'); // draft/approved/refunded/cancelled
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('UZS');
            $table->text('notes')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('customer_id');
            $table->index('sell_id');
            $table->index('status');
            $table->index('ticket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_returns');
    }
};
