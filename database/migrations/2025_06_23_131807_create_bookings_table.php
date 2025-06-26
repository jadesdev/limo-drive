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

            $table->foreignUuid('fleet_id')->nullable()->constrained('fleets')->nullOnDelete();
            $table->foreignUuid('driver_id')->nullable()->constrained('drivers')->nullOnDelete();
            $table->string('service_type')->nullable();
            $table->boolean('is_accessible')->default(false);
            $table->boolean('is_return_service')->default(false);
            $table->integer('duration_hours')->nullable();

            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_email');
            $table->string('customer_phone');

            $table->timestamp('pickup_datetime')->index();
            $table->text('pickup_address');
            $table->string('pickup_latitude')->nullable();
            $table->string('pickup_longitude')->nullable();

            $table->text('dropoff_address')->nullable();
            $table->string('dropoff_latitude')->nullable();
            $table->string('dropoff_longitude')->nullable();

            $table->unsignedInteger('passenger_count')->default(1);
            $table->unsignedInteger('bag_count')->default(0);

            $table->decimal('price', 10, 2);
            $table->string('payment_method')->nullable();
            $table->string('payment_status')->default('unpaid')->index();
            $table->text('notes')->nullable();

            $table->string('status')->default('pending_payment')->index();
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
