<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(AuctionTypeSeeder::class);
        $this->call(AuctionSeeder::class);
        $this->call(PigeonsSeeder::class);
        $this->call(VoucherSeeder::class);
        $this->call(ItemSeeder::class);
        //$this->call(BidSeeder::class);
    }
}
