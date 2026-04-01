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
        Schema::create('listing_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('listing_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->boolean('change_sign')->default(false);

            $table->timestamps();

            $table->unique(['listing_id', 'item_id']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('listing_item');
    }
};
