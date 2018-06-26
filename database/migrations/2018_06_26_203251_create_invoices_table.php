<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->unsigned();
            $table->unsignedInteger('created_by_code')->nullable();
            $table->unsignedInteger('updated_by_code')->nullable();
            $table->integer('branch_id')->unsigned();
            $table->string('client_name')->nullable();
            $table->string('client_email')->nullable();
            $table->string('status', 1)->default('A'); //I[nactive], A[ctive]
            $table->float('volumetric_weight');
            $table->float('real_weight');
            $table->float('total');
            $table->text("notes")->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
