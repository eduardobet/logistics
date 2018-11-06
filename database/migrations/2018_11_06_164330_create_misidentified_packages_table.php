<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMisidentifiedPackagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('misidentified_packages', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id');
            $table->integer('branch_to')->unsigned();
            $table->integer('client_id')->unsigned()->nullable();
            $table->integer('cargo_entry_id')->unsigned()->nullable();
            $table->string('trackings');
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
        Schema::dropIfExists('misidentified_packages');
    }
}
