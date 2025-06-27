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
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('language', 30)->nullable();
            $table->timestamp('last_active')->nullable();
            $table->unsignedInteger('bookings_count')->default(0);
            $table->timestamps();
        });
        // drop old cuttomer details from bookings
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['customer_first_name', 'customer_last_name', 'customer_email', 'customer_phone']);
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->uuid('customer_id')->nullable()->after('id');
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('customer_first_name');
            $table->string('customer_last_name');
            $table->string('customer_phone')->nullable();
            $table->string('customer_email');
        });
        Schema::dropIfExists('customers');
    }
};
