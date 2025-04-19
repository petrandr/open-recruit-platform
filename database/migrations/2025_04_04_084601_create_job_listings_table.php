<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title');
            $table->text('short_description');
            $table->text('headline'); // renamed from description
            $table->text('responsibilities')->nullable();
            $table->text('requirements')->nullable();
            $table->text('bonus')->nullable();
            $table->text('benefits')->nullable();
            $table->date('date_opened')->nullable();
            $table->string('job_type');
            $table->json('workplace')->nullable();
            $table->enum('status', ['active', 'inactive', 'draft', 'disable'])->default('draft');
            $table->string('location');
            $table->json('who_to_notify')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_listings');
    }
};
