<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->unsignedBigInteger('team_id')->nullable()->after('parent_id');
            $table->dropColumn('is_private');
        });

        Schema::dropIfExists('category_user');
    }

    public function down(): void
    {
        Schema::create('category_user', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('user_id');
            $table->primary(['category_id', 'user_id']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->boolean('is_private')->default(false);
            $table->dropColumn('team_id');
        });
    }
};
