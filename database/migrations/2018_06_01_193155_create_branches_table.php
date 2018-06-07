<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('tenant_id');
            $table->unsignedInteger('created_by_code')->nullable();
            $table->unsignedInteger('updated_by_code')->nullable();
            $table->string('name')->unique();
            $table->string('code');
            $table->string('address');
            $table->string('telephones');
            $table->string('emails');
            $table->string('faxes')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('logo')->nullable();
            $table->string('ruc')->nullable();
            $table->string('dv')->nullable();
            $table->string('status', 1)->default('A');
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
        Schema::dropIfExists('branches');
    }
}
