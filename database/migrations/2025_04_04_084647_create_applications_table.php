<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('applicant_id');
            $table->string('cv_path');
            $table->string('cv_disk')->nullable();
            $table->string('location'); // Current location at time of application
            $table->enum('status', ['submitted', 'under review', 'accepted', 'rejected'])->default('submitted');
            $table->boolean('rejection_sent')->default(false);
            $table->string('notice_period')->nullable();
            $table->decimal('desired_salary', 10, 2)->nullable();
            $table->string('salary_currency')->nullable();
            $table->string('linkedin_profile')->nullable();
            $table->string('github_profile')->nullable();
            $table->string('how_heard')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('job_id')->references('id')->on('job_listings')->onDelete('cascade');
            $table->foreign('applicant_id')->references('id')->on('candidates')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_applications');
    }
};
