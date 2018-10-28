<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Customer;
use App\Moment;
use App\Settings;
use App\Otp;
use Illuminate\Support\Str;

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
        DB::statement('TRUNCATE customers CASCADE');

        $faker = Factory::create('en_GH');

        $password = Hash::make('password');

        $customer = Customer::create([
            'uuid' => (string) Str::orderedUuid(),
       		'first_name' => 'Joel',
            'last_name' => 'Klo',
        	'email' => 'joeykay9@gmail.com',
        	'phone' => '+233274351093',
        	'jelion' => 'joeykay9',
            'active' => true,
            'avatar' => $faker->imageUrl,
        	'password' => $password,
        ]);

        $customer->settings()->save(new Settings);
        $customer->otp()->save(new Otp);

        for($i = 0; $i < 50; $i++) {
        	$customer = Customer::create([
                'uuid' => $faker->uuid,
        		'first_name' => $faker->firstName,
                'last_name' => $faker->lastName,
        		'email' => $faker->email,
        		'phone' => $faker->phoneNumber,
        		'jelion' => $faker->colorName,
                'active' => $faker->boolean,
                'avatar' => $faker->imageUrl,
        		'password' => $password,
        	]);

            $customer->settings()->save(new Settings);
            $customer->otp()->save(new Otp);
        }
    }
}
