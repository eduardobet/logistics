<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceiveWhMailColumnToExtraContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extra_contacts', function (Blueprint $table) {
            $table->boolean('receive_wh_mail')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('extra_contacts', function (Blueprint $table) {
            $table->dropColumn('receive_wh_mail');
        });
    }
}
