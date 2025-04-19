<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_screening_questions', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key
            $table->unsignedBigInteger('job_id');
            $table->text('question_text');
            $table->string('question_type')->nullable();
            $table->string('min_value')->nullable();
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('job_id')->references('id')->on('job_listings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_screening_questions');
    }
};
