<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('team_user', function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();

            $table->decimal('sharing_ratio', 5, 2)->default(1);
            $table->date('member_from')->nullable();
            $table->date('member_to')->nullable();

            $table->boolean('reveal_private')->default(false);
            $table->timestamp('user_apply_date')->nullable();
            $table->timestamp('team_accept_date')->nullable();

            $table->foreignId('clearing_account')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_user');
    }

};
