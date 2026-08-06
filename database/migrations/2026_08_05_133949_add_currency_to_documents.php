<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            // Currency reference (security ID)
            $table->unsignedBigInteger('currency_id')->nullable()->after('title');

            // Persisted FX rate at posting date
            $table->decimal('currency_rate', 26, 12)->nullable()->after('currency_id');

            // Optional FK (Derby may ignore enforcement, but Laravel stays consistent)
            $table->foreign('currency_id')->references('id')->on('securities');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['currency_id', 'currency_rate']);
        });
    }
};
