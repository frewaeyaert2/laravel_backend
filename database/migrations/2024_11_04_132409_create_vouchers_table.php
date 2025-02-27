<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained('auctions')->onDelete('cascade');
            $table->string('title');
            $table->string('enthusiast');
            $table->string('address_enthusiast');
            $table->dateTime('end_date');
            $table->boolean('published')->default(false);
            $table->string('image_path')->nullable(true);
            $table->string('description');
            $table->string('short_description');
            $table->timestamps();

            $table->unique(['auction_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
