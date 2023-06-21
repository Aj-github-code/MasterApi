<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovieCrewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movie_crews', function (Blueprint $table) {
            $table->increments('id');
            $table->string('movie_id');
            $table->string('crew_id');
            $table->string('role_id');
            $table->string('text_1');
            $table->string('text_2');
            $table->string('created_by');
            $table->string('updated_by');
            $table->string('is_active');
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
        Schema::dropIfExists('movie_crews');
    }
}
