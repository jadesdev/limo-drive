<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('code')->nullable();
            $table->string('payment_intent_id')->nullable()->index();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('gateway_name')->default('stripe');
            $table->string('gateway_ref')->unique();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->json('gateway_payload')->nullable();
            $table->timestamps();
            $table->index(['booking_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
