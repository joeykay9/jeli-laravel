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
            $table->integer('customer_id')->nullable();
            $table->string('category')->nullable();
            $table->string('title');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->string('location')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('budget')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });

        Schema::create('customer_moment', function (Blueprint $table) {
            $table->integer('customer_id');
            $table->integer('moment_id');
            $table->boolean('is_organiser')->default(0);
            $table->boolean('is_guest')->default(0);
            $table->boolean('is_grp_admin')->default(0);
            $table->timestamps();
            $table->primary(['customer_id', 'moment_id']);

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('moment_id')->references('id')->on('moments')->onDelete('cascade');
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
