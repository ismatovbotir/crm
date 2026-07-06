<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('name', 255);
            $table->string('legal_name', 255)->nullable();
            $table->string('inn', 20)->nullable()->unique();
            $table->string('oked', 20)->nullable();

            $table->foreignId('business_type_id')->nullable()
                ->constrained('business_types')->nullOnDelete();
            $table->string('segment', 10)->default('B');
            $table->string('status', 20)->default('active');

            $table->string('region', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('address')->nullable();

            $table->string('phone', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 255)->nullable();

            $table->foreignId('bank_id')->nullable()
                ->constrained('banks')->nullOnDelete();
            $table->string('bank_account', 20)->nullable();

            $table->decimal('credit_limit', 15, 2)->nullable();
            $table->unsignedSmallInteger('payment_terms_days')->nullable();
            $table->date('customer_since')->nullable();

            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index('status');
            $table->index('bank_id');
            $table->index('business_type_id');
            $table->index('segment');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
