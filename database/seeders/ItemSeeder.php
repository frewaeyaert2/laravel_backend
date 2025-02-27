<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\Pigeon;
use App\Models\Voucher;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Loop through all Pigeons and create associated Items
        Pigeon::all()->each(function ($pigeon) {
            Item::create([
                'auction_item_id' => $pigeon->id,
                'itemable_id' => $pigeon->id,
                'itemable_type' => Pigeon::class,
                'type' => 'pigeon',
                'auction_id' => $pigeon->auction_id, // Use related auction ID
                'highest_bid' => null, // Default to null for now
            ]);
        });

        // Loop through all Vouchers and create associated Items
        Voucher::all()->each(function ($voucher) {
            Item::create([
                'auction_item_id' => $voucher->id,
                'itemable_id' => $voucher->id,
                'itemable_type' => Voucher::class,
                'type' => 'voucher',
                'auction_id' => $voucher->auction_id, // Use related auction ID
                'highest_bid' => null, // Default to null for now
            ]);
        });
    }
}
