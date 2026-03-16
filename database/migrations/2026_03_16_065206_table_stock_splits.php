<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_splits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('old_id')
                ->constrained('securities')
                ->cascadeOnDelete();

            $table->foreignId('new_id')
                ->constrained('securities')
                ->cascadeOnDelete();

            $table->date('split_date');
            $table->decimal('split_factor', 12, 6);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_splits');
    }
};
