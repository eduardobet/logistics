<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignBranchIdToCargoEntries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cargo_entries', function (Blueprint $table) {
            $table->foreign('branch_id')
                ->references('id')->on('branches')
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
            $table->dropForeign('cargo_entries_branch_id_foreign');
        });
    }
}
