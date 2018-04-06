<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->increments('id');
            $table->string('description');
            $table->double('price', 8, 2);
            $table->string('status');
            $table->timestamps();
        });

        Schema::create('moment_service', function (Blueprint $table) {
            $table->integer('moment_id');
            $table->integer('service_id');
            $table->integer('quantity');
            $table->string('status');
            $table->timestamps();
            $table->primary(['moment_id', 'service_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('services');
        Schema::dropIfExists('moment_service');
    }
}
