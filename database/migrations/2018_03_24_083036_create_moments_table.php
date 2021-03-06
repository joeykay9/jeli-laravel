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
            $table->integer('customer_id')->nullable(); //I hope there is a reason I made this nullable lol
            $table->string('category')->nullable();
            $table->string('title');
            $table->string('icon')->nullable();
            $table->string('budget')->nullable();
            $table->boolean('is_memory')->default(0);
            $table->string('place_id')->nullable();
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade'); //to be thought through later
            // $table->foreign('place_id')->references('id')->on('places')->onDelete('set null');
        });

        Schema::create('customer_moment', function (Blueprint $table) {
            $table->integer('customer_id');
            $table->integer('moment_id');
            $table->boolean('is_organiser')->default(0);
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
