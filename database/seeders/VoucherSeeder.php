<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Faker\Factory as Faker;

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        DB::table("vouchers")->insert([
            [
                'auction_id' => 2,
                'title' => $faker->sentence(3),
                'enthusiast' => $faker->name,
                'address_enthusiast' => $faker->address,
                'end_date' => $faker->dateTimeBetween('+1 week', '+1 month'),
                'published' => $faker->boolean,
                'image_path' => $faker->imageUrl(640, 480, 'business', true),
                'description' => $faker->paragraph, 
                'short_description' => $faker->sentence(5), 
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'auction_id' => 2,
                'title' => $faker->sentence(3),
                'enthusiast' => $faker->name,
                'address_enthusiast' => $faker->address,
                'end_date' => $faker->dateTimeBetween('+1 week', '+1 month'),
                'published' => $faker->boolean,
                'image_path' => $faker->imageUrl(640, 480, 'business', true),
                'description' => $faker->paragraph, 
                'short_description' => $faker->sentence(5), 
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
        
    }
}
