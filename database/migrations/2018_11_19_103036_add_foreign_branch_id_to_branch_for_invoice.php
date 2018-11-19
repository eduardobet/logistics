<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignBranchIdToBranchForInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branch_for_invoice', function (Blueprint $table) {
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
        Schema::table('branch_for_invoice', function (Blueprint $table) {
            $table->dropForeign('branch_for_invoice_branch_id_foreign');
        });
    }
}
