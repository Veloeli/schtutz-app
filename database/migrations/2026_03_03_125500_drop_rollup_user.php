<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    
    public function up(): void
    {
        Schema::dropIfExists('rollup_user');
    }

    public function down(): void
    {
        Schema::create('rollup_user', function ($table) {
            $table->foreignId('rollup_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['rollup_id', 'user_id']);
        });
    }
};
