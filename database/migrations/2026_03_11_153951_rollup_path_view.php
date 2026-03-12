<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE OR REPLACE VIEW rollup_paths_view AS
            WITH RECURSIVE rollup_paths AS (
                -- Root nodes
                SELECT 
                    id,
                    parent_id,
                    code,
                    name,
                    user_id,
                    team_id,
                    name AS path,
                    id AS root_id,
                    user_id AS root_user_id,
                    team_id AS root_team_id,
                    0 AS depth
                FROM rollups
                WHERE parent_id IS NULL

                UNION ALL

                -- Children
                SELECT 
                    r.id,
                    r.parent_id,
                    r.code,
                    r.name,
                    r.user_id,
                    r.team_id,
                    CONCAT(rp.path, ' > ', r.name) AS path,
                    rp.root_id,
                    rp.root_user_id,
                    rp.root_team_id,
                    rp.depth + 1
                FROM rollups r
                JOIN rollup_paths rp ON r.parent_id = rp.id
            )
            SELECT *
            FROM rollup_paths;
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS rollup_paths_view");
    }
};
