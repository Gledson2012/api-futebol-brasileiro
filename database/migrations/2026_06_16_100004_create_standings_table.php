<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandingsTable extends Migration
{
    public function up()
    {
        Schema::create('standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_edition_id')->constrained('championship_editions')->onDelete('cascade');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->integer('position');
            $table->integer('points');
            $table->integer('played');
            $table->integer('won');
            $table->integer('drawn');
            $table->integer('lost');
            $table->integer('goals_for');
            $table->integer('goals_against');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('standings');
    }
}
