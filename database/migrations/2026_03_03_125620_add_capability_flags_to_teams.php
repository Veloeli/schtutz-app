<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->boolean('has_common_financials')->default(false);
            $table->boolean('has_common_reporting')->default(false);
            $table->boolean('has_common_securities')->default(false);
            $table->date('valid_from')->nullable();
            $table->date('valid_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropColumn([
                'has_common_financials',
                'has_common_reporting',
                'has_common_securities',
                'valid_from',
                'valid_until',
            ]);
        });
    }
};
