<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('document_id')
                  ->constrained()
                  ->cascadeOnDelete();
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['document_id']);
            $table->dropColumn('document_id');
        });
    }
};
