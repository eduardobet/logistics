<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddClientIdColumnToCargoEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cargo_entries', function (Blueprint $table) {
            $table->unsignedInteger('client_id')->nullable();

            $table->foreign('client_id')
                ->references('id')->on('clients')
                ->onDelete('cascade');
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
            $table->dropColumn('client_id');
            $table->dropForeign('cargo_entries_client_id_foreign');
        });
    }
}
