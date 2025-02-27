<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class AuctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        DB::table('auctions')->insert([
            [
                'auction_type_id' => 1,
                'name' => $faker->word,
                'description' => $faker->paragraph,
                'short_description' => $faker->sentence,
                'start_date' => $faker->dateTimeBetween('-1 month', '+1 month'),
                'end_date' => $faker->dateTimeBetween('+1 month', '+2 months'),
                'image_path' => $faker->imageUrl(640, 480, 'technics', true),
                'published' => $faker->boolean,
                'active' => $faker->boolean,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'auction_type_id' => 2,
                'name' => $faker->word,
                'description' => $faker->paragraph,
                'short_description' => $faker->sentence,
                'start_date' => $faker->dateTimeBetween('-1 month', '+1 month'),
                'end_date' => $faker->dateTimeBetween('+1 month', '+2 months'),
                'image_path' => $faker->imageUrl(640, 480, 'animals', true),
                'published' => $faker->boolean,
                'active' => $faker->boolean,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

    }
}
