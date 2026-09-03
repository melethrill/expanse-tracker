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
        Schema::create('family_members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->date('dob');
            $table->string('family_register_number')->nullable();
            $table->string('amka')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('pa_number')->nullable();
            $table->string('id_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('id_image_front')->nullable();
            $table->string('id_image_back')->nullable();
            $table->string('passport_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('family_members');
    }
};
