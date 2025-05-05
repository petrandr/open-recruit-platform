<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('application_id');
            $table->unsignedBigInteger('interviewer_id')->nullable();
            $table->dateTime('scheduled_at')->nullable();
            $table->enum('status', array_keys(\App\Support\Interview::statuses()))->default('scheduled');
            $table->enum('round', array_keys(\App\Support\Interview::rounds()))->comment('Interview round, e.g., Phone Screen, Technical, HR');
            $table->enum('mode', array_keys(\App\Support\Interview::modes()))->comment('Interview mode');
            $table->string('location')->nullable()->comment('Location or link for the interview');
            $table->integer('duration_minutes')->nullable()->comment('Duration in minutes');
            $table->text('comments')->nullable()->comment('Interviewer comments or feedback');
            $table->timestamps();

            $table->foreign('application_id')
                  ->references('id')->on('job_applications')
                  ->onDelete('cascade');

            $table->foreign('interviewer_id')
                  ->references('id')->on('users')
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('interviews');
    }
};
