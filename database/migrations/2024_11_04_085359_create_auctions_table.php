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
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_type_id')->constrained('auction_types')->onDelete('cascade');
            $table->string('name');
            $table->string('description');
            $table->string('short_description');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('image_path')->nullable();
            $table->boolean('published')->default(false);
            $table->boolean('active')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
