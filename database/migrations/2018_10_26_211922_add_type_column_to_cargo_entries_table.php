<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTypeColumnToCargoEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cargo_entries', function (Blueprint $table) {
            $table->string('type', 1)->nullable(); //[N]ormal, M[isidentified]
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cargo_entries', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
