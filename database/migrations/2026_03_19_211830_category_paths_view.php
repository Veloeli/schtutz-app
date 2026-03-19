<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing view if it exists (MySQL/MariaDB safe)
        DB::statement('DROP VIEW IF EXISTS category_paths_view');

        // Create or replace the view
        DB::statement("
            CREATE VIEW category_paths_view AS
            SELECT * FROM (
                SELECT
                    rp.root_id,
                    rp.id AS rollup_id,
                    rp.parent_id,
                    rp.root_user_id,
                    rp.root_team_id,
                    rp.code,
                    rp.name,
                    rp.path,
                    rp.depth,
                    rc.category_id,
                    c.type,
                    c.user_id,
                    c.team_id,
                    'rollup' AS src
                FROM rollup_paths_view rp
                JOIN rollup_category rc ON rp.id = rc.rollup_id
                JOIN categories c ON rc.category_id = c.id

                UNION

                SELECT
                    rp.root_id,
                    rp.id AS rollup_id,
                    rp.parent_id,
                    rp.root_user_id,
                    rp.root_team_id,
                    c.code,
                    c.name,
                    CONCAT(rp.path, ' > ', c.name) AS path,
                    rp.depth + 1 AS depth,
                    rc.category_id,
                    c.type,
                    c.user_id,
                    c.team_id,
                    'category' AS src
                FROM rollup_paths_view rp
                JOIN rollup_category rc ON rp.id = rc.rollup_id
                JOIN categories c ON rc.category_id = c.id
                WHERE rc.is_genuine = 1
            ) x
        ");

        // Add index on items table
        Schema::table('items', function ($table) {
            $table->index(
                ['category_id', 'document_id', 'amount', 'quantity'],
                'idx_items_cat_doc_amount_quantity'
            );
        });
    }

    public function down(): void
    {
        // Drop the view
        DB::statement('DROP VIEW IF EXISTS category_paths_view');

        // Drop the index
        Schema::table('items', function ($table) {
            $table->dropIndex('idx_items_cat_doc_amount_quantity');
        });
    }
};
