<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->unsigned();
            $table->unsignedInteger('created_by_code')->nullable();
            $table->unsignedInteger('updated_by_code')->nullable();
            $table->unsignedInteger('branch_from');
            $table->unsignedInteger('branch_to');
            $table->unsignedInteger('client_id')->nullable();
            $table->string('type', 1);
            $table->unsignedInteger('mailer_id')->nullable();
            $table->unsignedInteger('qty');
            $table->text('trackings')->nullable();
            $table->text('reference')->nullable();
            $table->integer('tot_packages')->default(0);
            $table->integer('tot_weight')->default(0);
            $table->string('status', 1)->default('A'); //I[nactive], A[ctive]

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
        Schema::dropIfExists('warehouses');
    }
}
