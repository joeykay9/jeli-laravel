<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Moment;
use App\Place;
use App\Schedule;

class MomentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('TRUNCATE moments CASCADE');

        $faker = Factory::create('en_US');

        for ($i=0; $i < 5; $i++) { 
        	$moment = Moment::create([
        		'customer_id' => $i+1,
        		'category' => $faker->word,
        		'title' => $faker->word,
        		'icon' => $faker->imageUrl,
        		'budget' => mt_rand(100, 999999),
        		'is_memory' => $faker->boolean,
        	]);

        	$moment->members()->attach($moment, ['customer_id' => $moment->customer_id,'is_organiser' => true, 'is_grp_admin' => true]);

            $place = Place::create([
                'place_id' => $faker->word,
                'place_name' => $faker->streetName,
                'place_image' => $faker->imageUrl,
            ]);

            $place->moments()->save($moment);

            $schedule = new Schedule([
                'start_time' => $faker->date,
                'end_date' => $faker->date,
                'start_time' => $faker->time,
                'end_time' => $faker->time,
            ]);

            $moment->schedules()->save($schedule);

        	for ($j=0; $j < 5 ; $j++) {
        		$random = mt_rand(1,50);

        		$moment->members()->attach($moment, [
        			'customer_id' => ($random == $moment->customer_id ? mt_rand(1,50) : $random),
        			'is_organiser' => $faker->boolean,
        		]);
        	}
        }
    }
}
