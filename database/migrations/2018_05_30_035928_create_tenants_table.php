<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTenantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('country_id');
            $table->string('domain')->unique();
            $table->string('name')->unique()->nullable();
            $table->string('status', 1);
            $table->string('lang', 2)->nullable();
            $table->string('telephones')->nullable();
            $table->string('emails')->nullable();
            $table->string('ruc')->nullable();
            $table->string('dv')->nullable();
            $table->string('address')->nullable();
            $table->string('logo')->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
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
        Schema::dropIfExists('tenants');
    }
}
