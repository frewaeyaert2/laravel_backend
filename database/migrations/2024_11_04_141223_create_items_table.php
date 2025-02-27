<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('auction_item_id');
            $table->morphs('itemable');
            $table->enum('type', ['pigeon', 'voucher']);
            $table->foreignId('auction_id')->constrained(table: 'auctions')->onDelete('cascade');
            $table->integer('highest_bid')->nullable(); 
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
