<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class AuctionTypeSeeder extends Seeder
{
    public function run()
    {
        DB::table('auction_types')->insert([
            [
                'type' => 'pigeon',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'voucher',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
