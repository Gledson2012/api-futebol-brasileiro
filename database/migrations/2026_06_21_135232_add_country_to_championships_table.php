<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCountryToChampionshipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->string('country')->nullable()->after('type');
        });
    }

    public function down()
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropColumn('country');
        });
    }
}
