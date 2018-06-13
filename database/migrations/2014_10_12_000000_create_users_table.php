<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->unsigned()->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('type', 1); // A[dmin], E[mployee], U[App user]
            $table->string('status', 1)->default('L'); // L[ocked], I[nactive], A[ctive]
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('token')->nullable();
            $table->string('avatar')->nullable();
            $table->string('full_name')->nullable();
            $table->string('pid')->nullable();
            $table->string('telephones')->nullable();
            $table->integer('position')->unsigned()->nullable();
            $table->boolean('is_main_admin')->default(false)->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
