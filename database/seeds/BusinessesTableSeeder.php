<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Business;

class BusinessesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
         Business::truncate();

        $faker = Factory::create();

        $password = Hash::make('business');

        Business::create([
       		'name' => 'Phronesis Ventures',
       		'country' => 'Ghana',
       		'location' => 'Accra',
        	'email' => 'phronesis@example.com',
        	'phone' => '0202821451',
        	'password' => $password,
        ]);

        for($i = 0; $i < 50; $i++) {
        	Business::create([
        		'name' => $faker->company,
        		'country' => $faker->country,
        		'location' => $faker->address,
        		'email' => $faker->email,
        		'phone' => $faker->phoneNumber,
        		'password' => $password,
        	]);
        }
    }
}
