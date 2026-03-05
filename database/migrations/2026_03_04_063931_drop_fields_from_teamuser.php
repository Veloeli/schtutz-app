<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            $table->dropColumn('user_apply_date');
            $table->dropColumn('team_accept_date');
        });
    }

    public function down(): void
    {
        Schema::table('team_user', function (Blueprint $table) {
            // Recreate the columns
            $table->timestamp('user_apply_date')->nullable();
            $table->timestamp('team_accept_date')->nullable();
        });
    }
};
