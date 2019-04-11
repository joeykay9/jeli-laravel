<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->unique();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->unique();
            $table->string('email')->unique()->nullable();
            $table->date('dob')->nullable();
            $table->string('jelion')->nullable();
            $table->string('avatar')->nullable();
            $table->string('password');
            $table->boolean('verified')->default(0);
            $table->boolean('active')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('customer_contact', function (Blueprint $table) {
            $table->integer('customer_id');
            $table->integer('contact_id');
            $table->string('contact_name');
            $table->timestamps();
            $table->primary(['customer_id', 'contact_id']);

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customers');
    }
}
