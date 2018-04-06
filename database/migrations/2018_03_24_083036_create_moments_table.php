<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMomentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('moments', function (Blueprint $table) {
            $table->increments('id');
            $table->string('category');
            $table->string('title');
            $table->string('icon');
            $table->timestamps();
        });

        Schema::create('customer_moment', function (Blueprint $table) {
            $table->integer('customer_id');
            $table->integer('moment_id');
            $table->string('category');
            $table->date('joined_at');
            $table->timestamps();
            $table->primary(['customer_id', 'moment_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('moments');
        Schema::dropIfExists('customer_moment');
    }
}
