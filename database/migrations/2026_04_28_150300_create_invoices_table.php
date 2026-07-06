<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();
            $table->string("agreement_number")->nullable(); // agreement number Accountant
            $table->integer("batch_number")->default(1); // batch number спецификация для 1С
            
            $table->foreignId('quote_id')->nullable()->constrained('quotes')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('currency', 3)->default('UZS');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->string('status', 30)->default('draft'); // draft/sent/partially_paid/paid/overdue/cancelled
            $table->date('due_date')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(12); // 12% НДС
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->string('shipment_status', 20)->default('none'); // none / partial / complete
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index('customer_id');
            $table->index('manager_id');
            $table->index('status');
            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
