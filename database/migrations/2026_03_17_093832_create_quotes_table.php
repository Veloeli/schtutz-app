<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('security_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('quote_date');
            $table->decimal('price', 20, 6);

            $table->timestamps();

            $table->unique(['security_id', 'quote_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
