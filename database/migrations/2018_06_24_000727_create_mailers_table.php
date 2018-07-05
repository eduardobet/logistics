<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMailersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->unsigned();
            $table->unsignedInteger('created_by_code')->nullable();
            $table->unsignedInteger('updated_by_code')->nullable();
            $table->string('name');
            $table->string('status', 1)->default('A'); //I[nactive], A[ctive]
            $table->text('description')->nullable();
            $table->float('vol_price')->nullable()->default(0);
            $table->float('real_price')->nullable()->default(0);
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
        Schema::dropIfExists('mailers');
    }
}
