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
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code')->unique();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->timestamp('pickup_datetime');
            $table->text('pickup_address');
            $table->text('dropoff_address')->nullable();
            $table->integer('passenger_count')->default(1);
            $table->integer('bag_count')->default(0);
            $table->text('notes_for_driver')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('status')->default('pending_payment')->index();
            $table->string('payment_status')->default('unpaid')->index();
            $table->foreignUuid('fleet_id')->nullable()->constrained('fleets')->nullOnDelete();
            $table->foreignUuid('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
