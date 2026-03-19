<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Rollup;
use App\Models\RollupCategory;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add the new column if it doesn't exist
        Schema::table('rollup_category', function (Blueprint $table) {
            if (!Schema::hasColumn('rollup_category', 'is_genuine')) {
                $table->boolean('is_genuine')->default(true)->after('category_id');
            }
        });

        // 2. Mark all existing rows as genuine
        DB::table('rollup_category')->update(['is_genuine' => true]);

        // 3. Rebuild inherited mappings
        $this->rebuildInheritedMappings();
    }

    public function down(): void
    {
        Schema::table('rollup_category', function (Blueprint $table) {
            if (Schema::hasColumn('rollup_category', 'is_genuine')) {
                $table->dropColumn('is_genuine');
            }
        });
    }

    private function rebuildInheritedMappings(): void
    {
        RollupCategory::where('is_genuine', true)
            ->orderBy('rollup_id')
            ->chunk(500, function ($assignments) {
                foreach ($assignments as $rc) {
                    $this->propagateUp($rc->rollup_id, $rc->category_id);
                }
            });
    }

    private function propagateUp(int $rollupId, int $categoryId): void
    {
        $current = $rollupId;

        while ($current !== null) {
            RollupCategory::firstOrCreate(
                [
                    'rollup_id'   => $current,
                    'category_id' => $categoryId,
                ],
                [
                    'is_genuine' => false,
                ]
            );

            $current = Rollup::where('id', $current)->value('parent_id');
        }
    }
};
