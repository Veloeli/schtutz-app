<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('quantity', 10, 3)->nullable();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
        });
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['quantity', 'category_id']);
        });
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->nullable(false)->change();
        });
    }
};
