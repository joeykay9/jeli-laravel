<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Customer;

class CustomersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        Customer::truncate();

        $faker = Factory::create();

        $password = Hash::make('password');

        Customer::create([
       		'name' => 'Joel Klo',
        	'email' => 'joeykay9@gmail.com',
        	'phone' => '0274351093',
        	'jelion' => 'joeykay9',
        	'password' => $password,
        ]);

        for($i = 0; $i < 50; $i++) {
        	Customer::create([
        		'name' => $faker->name,
        		'email' => $faker->email,
        		'phone' => $faker->phoneNumber,
        		'jelion' => $faker->colorName,
        		'password' => $password,
        	]);
        }
    }
}
