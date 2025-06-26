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
        Schema::table('fleets', function (Blueprint $table) {
            $table->decimal('base_fee', 8, 2)->default(0.00)->after('bags');
            $table->decimal('rate_per_mile', 8, 2)->default(0.00)->after('base_fee');
            $table->decimal('rate_per_hour', 8, 2)->default(0.00)->after('rate_per_mile');
            $table->unsignedInteger('minimum_hours')->default(1)->after('rate_per_hour');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fleets', function (Blueprint $table) {
            $table->dropColumn([
                'base_fee',
                'rate_per_mile',
                'rate_per_hour',
                'minimum_hours',
            ]);
        });
    }
};
