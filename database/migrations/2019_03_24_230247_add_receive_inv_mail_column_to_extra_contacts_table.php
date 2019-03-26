<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceiveInvMailColumnToExtraContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('extra_contacts', function (Blueprint $table) {
            $table->boolean('receive_inv_mail')->nullable()->default(false);
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
            $table->dropColumn( 'receive_inv_mail');
        });
    }
}
