<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('lms_id')->index();
            $table->string('username')->nullable()->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('firebase_id')->nullable();
            $table->string('full_name')->nullable()->index();
            $table->string('code')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('gender')->nullable();
            $table->dateTime('dob')->nullable();
            $table->string('image_url')->nullable();
            $table->string('address')->nullable();
            $table->string('facebook')->nullable();
            $table->string('notes')->nullable();
            $table->boolean('is_active')->nullable();
            $table->string('created_by')->nullable();
            $table->string('last_updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('teachers');
    }
};
