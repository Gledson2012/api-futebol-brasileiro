<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChampionshipsTable extends Migration
{
    public function up()
    {
        Schema::create('championships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type')->default('points')->comment('points, knockout, mixed');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('championships');
    }
}
