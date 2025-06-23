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
        Schema::create('services', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique()->index();
            $table->string('banner_image')->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('problem_solved_image')->nullable();
            $table->string('problem_solved_desc')->nullable();
            $table->string('target_audience_image')->nullable();
            $table->string('target_audience_desc')->nullable();
            $table->string('client_benefits_image')->nullable();
            $table->string('client_benefits_desc')->nullable();
            $table->json('features')->nullable();
            $table->json('technologies')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('order')->default(0)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
