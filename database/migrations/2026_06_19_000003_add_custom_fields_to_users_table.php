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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('gender')->nullable();
            $table->date('birthday')->nullable();
            $table->string('device_id')->nullable()->unique();
            $table->string('blood_type')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->text('allergies')->nullable();
            $table->string('role')->default('citizen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'address',
                'gender',
                'birthday',
                'device_id',
                'blood_type',
                'height',
                'weight',
                'allergies',
                'role',
            ]);
        });
    }
};
