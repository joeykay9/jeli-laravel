<?php

use Illuminate\Database\Seeder;
use Faker\Factory;
use App\Moment;

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

        for ($i=0; $i < 10; $i++) { 
        	$moment = Moment::create([
        		'customer_id' => $i+1,
        		'category' => $faker->word,
        		'title' => $faker->word,
        		'icon' => $faker->imageUrl,
        		'budget' => mt_rand(100, 999999),
        		'is_memory' => $faker->boolean,
        	]);

        	$moment->members()->attach($moment, ['customer_id' => $moment->customer_id,'is_organiser' => true, 'is_grp_admin' => true]);

        	for ($j=0; $j < 5 ; $j++) {
        		$random = mt_rand(1,50);
        		$is_organiser = $faker->boolean;

        		$moment->members()->attach($moment, [
        			'customer_id' => ($random == $moment->customer_id ? mt_rand(1,50) : $random),
        			'is_organiser' => $is_organiser,
        			'is_guest' => !($is_organiser),
        		]);
        	}
        }
    }
}
