<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        /**
         * 1) Drop foreign keys from the old table `rollup`
         */
        Schema::table('rollup', function (Blueprint $table) {
            $table->dropForeign('rollup_parent_id_foreign');
            $table->dropForeign('rollup_user_id_foreign');
        });

        /**
         * 2) Rename table
         */
        Schema::rename('rollup', 'rollups');

        /**
         * 3) Recreate foreign keys on the new table `rollups`
         */
        Schema::table('rollups', function (Blueprint $table) {
            // parent_id → rollups.id
            $table->foreign('parent_id', 'rollups_parent_id_foreign')
                ->references('id')
                ->on('rollups')
                ->restrictOnDelete()
                ->restrictOnUpdate();

            // user_id → users.id
            $table->foreign('user_id', 'rollups_user_id_foreign')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    public function down()
    {
        /**
         * Reverse the process
         */

        Schema::table('rollups', function (Blueprint $table) {
            $table->dropForeign('rollups_parent_id_foreign');
            $table->dropForeign('rollups_user_id_foreign');
        });

        Schema::rename('rollups', 'rollup');

        Schema::table('rollup', function (Blueprint $table) {
            $table->foreign('parent_id', 'rollup_parent_id_foreign')
                ->references('id')
                ->on('rollup')
                ->cascadeOnDelete()
                ->restrictOnUpdate();

            $table->foreign('user_id', 'rollup_user_id_foreign')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete()
                ->restrictOnUpdate();
        });
    }
};
