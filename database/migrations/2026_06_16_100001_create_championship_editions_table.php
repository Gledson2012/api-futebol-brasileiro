<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChampionshipEditionsTable extends Migration
{
    public function up()
    {
        Schema::create('championship_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('championship_id')->constrained()->onDelete('cascade');
            $table->year('year');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('championship_editions');
    }
}
