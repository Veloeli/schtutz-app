<?php

namespace App\Observers;

use App\Models\Rollup;
use App\Models\RollupCategory;

class RollupCategoryObserver
{
    public function created(RollupCategory $rc): void
    {
        if (! $rc->is_genuine) {
            return;
        }

        $current = $rc->rollup_id;

        while ($current !== null) {
            RollupCategory::firstOrCreate(
                [
                    'rollup_id'   => $current,
                    'category_id' => $rc->category_id,
                ],
                [
                    'is_genuine' => false,
                ]
            );

            $current = Rollup::where('id', $current)->value('parent_id');
        }
    }

    public function deleted(RollupCategory $rc): void
    {
        if (! $rc->is_genuine) {
            return;
        }

        $current = $rc->rollup_id;

        while ($current !== null) {
            RollupCategory::where('rollup_id', $current)
                ->where('category_id', $rc->category_id)
                ->where('is_genuine', false)
                ->delete();

            $current = Rollup::where('id', $current)->value('parent_id');
        }
    }
}
