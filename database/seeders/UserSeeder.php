<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create();

        for ($i = 0; $i < 3; $i++) {
            User::create([
                "email" => $faker->unique()->safeEmail,
                "password" => bcrypt("password"),
                "last_name" => $faker->lastName,
                "first_name" => $faker->firstName,
                "telephone_number" => $faker->phoneNumber,
                "street" => $faker->streetName,
                "house_number" => $faker->buildingNumber, // Correct attribute name
                "unit_number" => $faker->optional()->bothify('??##'), // Correct attribute name
                "postcode" => $faker->postcode,
                "city" => $faker->city,
                "country" => $faker->country,
            ]);
        }
    }
}
