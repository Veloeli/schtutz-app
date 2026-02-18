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
        Schema::create('rollup_category', function (Blueprint $table) {
            $table->unsignedBigInteger('rollup_id');
            $table->unsignedBigInteger('category_id');

            $table->primary(['rollup_id', 'category_id']);

            $table->foreign('rollup_id')
                ->references('id')
                ->on('rollup')
                ->onDelete('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rollup_category');
    }
};
