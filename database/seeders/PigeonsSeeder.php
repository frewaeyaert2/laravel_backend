<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class PigeonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        DB::table("pigeons")->insert([
            [
                'auction_id' => 1,
                'title' => $faker->sentence(3),
                'short_description' => $faker->sentence(5),
                'description' => $faker->paragraph,
                'ring_number' => $faker->numerify('########'),
                'color' => $faker->colorName,
                'sex' => $faker->randomElement(['male', 'female']),
                'end_date' => $faker->dateTimeBetween('+1 week', '+1 month'),
                'name' => $faker->firstName,
                'published' => $faker->boolean,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'auction_id' => 1, // Corrected duplicated field name
                'title' => $faker->sentence(3),
                'short_description' => $faker->sentence(5),
                'description' => $faker->paragraph,
                'ring_number' => $faker->numerify('########'),
                'color' => $faker->colorName,
                'sex' => $faker->randomElement(['male', 'female']),
                'end_date' => $faker->dateTimeBetween('+1 week', '+1 month'),
                'name' => $faker->firstName,
                'published' => $faker->boolean,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
