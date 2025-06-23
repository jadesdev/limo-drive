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
        Schema::create('drivers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->string('language')->nullable();
            $table->string('profile_image')->nullable();
            $table->date('dob')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->enum('status', ['active', 'inactive', 'on_leave', 'suspended'])->default('active');
            $table->integer('orders')->default(0);
            $table->date('hire_date')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamp('last_online_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['status', 'is_available']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
