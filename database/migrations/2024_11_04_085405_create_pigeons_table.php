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
        Schema::create('pigeons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained('auctions')->onDelete('cascade');
            $table->string('title');
            $table->string('name');
            $table->string('short_description');
            $table->string('description');
            $table->string('ring_number')->unique();
            $table->string('color');
            $table->string('sex');
            $table->dateTime('end_date');
            $table->boolean('published')->default(false);
            $table->timestamps();

            $table->unique(['auction_id', 'title']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pigeons');
    }
};
