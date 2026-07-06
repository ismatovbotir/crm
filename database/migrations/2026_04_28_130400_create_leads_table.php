<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            // Контактная информация
            $table->string('name', 255);
            $table->string('company', 255)->nullable();
            $table->foreignId('customer_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->string('phone', 20);
            $table->string('email', 255)->nullable();

            // Источник, менеджер и автор
            $table->foreignId('source_id')
                ->constrained('lead_sources')->restrictOnDelete();
            $table->foreignId('manager_id')
                ->constrained('users')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()
                ->constrained('users')->nullOnDelete();

            // Статус и квалификация
            $table->string('status', 20)->default('new');
            // new / qualified / contacted / in_negotiation / won / lost
            $table->unsignedTinyInteger('score')->nullable(); // 1-10
            $table->decimal('budget', 15, 2)->nullable();
            $table->foreignId('business_type_id')->nullable()
                ->constrained('business_types')->nullOnDelete();
            $table->string('region', 100)->nullable();

            // Финал сделки
            $table->timestamp('converted_at')->nullable();
            $table->decimal('won_amount', 15, 2)->nullable();
            $table->string('lost_reason', 50)->nullable();
            // price / timing / competitor / other

            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Индексы
            $table->index('status');
            $table->index('source_id');
            $table->index('manager_id');
            $table->index('created_by');
            $table->index('customer_id');
            $table->index('business_type_id');
            $table->index('phone'); // для дедупликации
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
