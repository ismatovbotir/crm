<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sells', function (Blueprint $table) {
            $table->id();
            $table->string('number', 50)->unique();

            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('status', 20)->default('draft'); // draft/confirmed/shipped/delivered/cancelled
            $table->date('sold_at')->nullable();

            $table->string('currency', 3)->default('UZS');
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('invoice_id');
            $table->index('customer_id');
            $table->index('manager_id');
            $table->index('status');
            $table->index('sold_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sells');
    }
};
