<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::dropIfExists('deputies');
    }

    public function down(): void
    {
        Schema::create('deputies', function ($table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('deputy_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
