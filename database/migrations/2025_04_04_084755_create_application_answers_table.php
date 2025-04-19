<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_application_answers', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('question_id');
            $table->text('answer_text')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('application_id')->references('id')->on('job_applications')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('job_screening_questions')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_application_answers');
    }
};
