<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
class BidSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        DB::table("bids")->insert([
            [
                'user_id' => 1, 
                'date_time' => $faker->dateTimeBetween('-1 week', 'now'), 
                'bid' => $faker->randomFloat(2, 10, 1000),
                'item_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2, 
                'date_time' => $faker->dateTimeBetween('-1 week', 'now'), 
                'bid' => $faker->randomFloat(2, 10, 1000),
                'item_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3, 
                'date_time' => $faker->dateTimeBetween('-1 week', 'now'), 
                'bid' => $faker->randomFloat(2, 10, 1000),
                'item_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
