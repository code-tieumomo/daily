<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('lms_id')->unique();
            $table->string('name')->unique();
            $table->string('short_name')->unique();
            $table->boolean('is_active')->nullable();
            $table->boolean('is_ready')->nullable();
            $table->integer('number_of_session')->nullable();
            $table->integer('session_hour')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('courses');
    }
};
