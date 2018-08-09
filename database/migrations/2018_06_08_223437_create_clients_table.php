<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('tenant_id')->unsigned();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name')->nullable();
            $table->string('type', 1); // C[ommon], E[nterprise], V[endor]
            $table->string('status', 1)->default('A'); //I[nactive], A[ctive]
            $table->string('email')->nullable()->unique();
            $table->string('telephones');
            $table->string('org_name')->nullable();
            $table->string('pid');
            $table->unsignedInteger('created_by_code')->nullable();
            $table->unsignedInteger('updated_by_code')->nullable();

            $table->unsignedInteger('country_id')->nullable();
            $table->unsignedInteger('department_id')->nullable();
            $table->unsignedInteger('city_id')->nullable();
            $table->text('address')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('pay_volume')->nullable()->default(false);
            $table->boolean('special_rate')->nullable()->default(false);
            $table->boolean('special_maritime')->nullable()->default(false);
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
        Schema::dropIfExists('clients');
    }
}
