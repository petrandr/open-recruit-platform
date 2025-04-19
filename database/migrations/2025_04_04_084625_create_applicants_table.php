<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->bigIncrements('id'); // Primary Key
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('mobile_number');
            $table->timestamps();
        });
        // Full-text search index on name and email
        DB::statement("CREATE INDEX candidates_search_idx ON candidates USING GIN (to_tsvector('english', first_name || ' ' || last_name || ' ' || email));");
    }

    public function down()
    {
        // Drop full-text search index
        DB::statement("DROP INDEX IF EXISTS candidates_search_idx;");
        Schema::dropIfExists('candidates');
    }
};
