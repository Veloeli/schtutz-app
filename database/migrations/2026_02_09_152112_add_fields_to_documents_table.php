<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->bigInteger('old_id')->nullable()->after('id');
            $table->date('posting_date')->after('old_id');
            $table->smallInteger('repeat_pattern')->nullable()->after('posting_date');
            $table->boolean('repeat_constant')->default(false)->after('repeat_pattern');
            $table->char('wizard', 1)->nullable()->after('repeat_constant');

            $table->foreignId('owner_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->after('wizard');
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'old_id',
                'posting_date',
                'repeat_pattern',
                'repeat_constant',
                'wizard',
            ]);

            $table->dropForeign(['owner_id']);
            $table->dropColumn('owner_id');
        });
    }

};
