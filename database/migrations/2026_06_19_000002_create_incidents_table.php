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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('category'); // Fire, Flood, Medical, Landslide, Earthquake, Other
            $table->string('severity'); // Low, Medium, High
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('address')->nullable();
            $table->string('status')->default('Processing'); // Dispatched, Processing, Resolved, Dismissed, In Progress
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('reporter_name')->nullable();
            $table->string('reporter_phone')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
