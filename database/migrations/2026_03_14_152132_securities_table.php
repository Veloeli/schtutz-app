<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('securities', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignId('team_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->string('isin')->nullable();
            $table->string('ticker')->nullable();
            $table->string('identifier')->nullable();
            $table->string('company')->nullable();
            $table->string('asset_class', 2)->nullable();

            // FK to securities.id (currency)
            $table->foreignId('currency_id')
                ->nullable()
                ->constrained('securities')
                ->nullOnDelete();

            $table->boolean('is_in_use')->default(true);
            $table->boolean('is_tracked')->default(true);
            $table->boolean('is_hedged')->default(false);

            $table->string('region')->nullable();
            $table->string('sector')->nullable();
            $table->string('strategy')->nullable();
            $table->string('theme')->nullable();

            // Option-specific fields
            $table->enum('option_type', ['CALL', 'PUT'])->nullable();

            $table->foreignId('option_underlying_id')
                ->nullable()
                ->constrained('securities')
                ->nullOnDelete();

            $table->decimal('option_strike', 18, 6)->nullable();
            $table->decimal('option_multiplier', 18, 6)->nullable();

            $table->boolean('option_calculate')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('securities');
    }
};
