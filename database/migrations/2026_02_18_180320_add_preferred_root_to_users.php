<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('preferred_root_id')
                ->nullable()
                ->constrained('rollup');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['preferred_root_id']);

            // Then drop the column
            $table->dropColumn('preferred_root_id');
        });
    }
};
