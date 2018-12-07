<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExtraMaritimePriceToClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->float('maritime_price')->nullable()->default(0);
            $table->boolean('pay_extra_maritime_price')->nullable()->default(false);
            $table->float('extra_maritime_price')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn('maritime_price');
            $table->dropColumn('pay_extra_maritime_price');
            $table->dropColumn('extra_maritime_price');
        });
    }
}
