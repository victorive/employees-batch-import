<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->integer('emp_id')->unique();
            $table->string('name_prefix', 10)->nullable();
            $table->string('first_name');
            $table->string('middle_initial', 1)->nullable();
            $table->string('last_name');
            $table->string('gender', 1)->nullable();
            $table->string('email')->unique();
            $table->date('date_of_birth');
            $table->time('time_of_birth')->nullable();
            $table->decimal('age_in_yrs', 4, 2)->nullable();
            $table->date('date_of_joining');
            $table->decimal('age_in_company_yrs', 5, 2)->nullable();
            $table->string('phone_number');
            $table->string('place_name')->nullable();
            $table->string('county')->nullable();
            $table->string('city')->nullable();
            $table->string('zip')->nullable();
            $table->string('region')->nullable();
            $table->string('user_name')->nullable();
            $table->timestamps();

            $table->index(['last_name', 'first_name']);
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
