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
        Schema::create('collections', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('team_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->date('date_from');
            $table->date('date_to')->nullable();

            $table->timestamps();
        });

        Schema::create('collection_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('collection_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('item_id')
                ->constrained()
                ->restrictOnDelete();

            $table->boolean('change_sign')->default(false);

            $table->timestamps();

            $table->unique(['collection_id', 'item_id']); 
        });

        Schema::dropIfExists('listing_item');
        Schema::dropIfExists('listings');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collections');
        Schema::dropIfExists('collection_item');
    }
};
