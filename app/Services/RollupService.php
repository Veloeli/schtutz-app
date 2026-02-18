<?php

namespace App\Services;

use App\Models\Rollup;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RollupService
{
    /*
    |--------------------------------------------------------------------------
    | Create a root node
    |--------------------------------------------------------------------------
    */
    public function createRoot(array $data, User $creator): Rollup
    {
        return DB::transaction(function () use ($data, $creator) {

            $rollup = Rollup::create([
                'name'      => $data['name'],
                'code'      => $data['code'] ?? null,
                'parent_id' => null,
                'user_id'   => $creator->id, // informational
            ]);

            // attach creator as a user with access
            $rollup->users()->attach($creator->id);

            return $rollup;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Create a child node
    |--------------------------------------------------------------------------
    */
    public function createChild(Rollup $parent, array $data): Rollup
    {
        if (!$parent->exists) {
            throw ValidationException::withMessages([
                'parent_id' => 'Parent rollup does not exist.',
            ]);
        }

        return Rollup::create([
            'name'      => $data['name'],
            'code'      => $data['code'] ?? null,
            'parent_id' => $parent->id,
            'user_id'   => $parent->user_id, // inherit informational creator
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update a rollup node
    |--------------------------------------------------------------------------
    */
    public function update(Rollup $rollup, array $data): Rollup
    {
        $rollup->update([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
        ]);

        return $rollup;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete a rollup node
    |--------------------------------------------------------------------------
    */
    public function delete(Rollup $rollup): void
    {
        $rollup->delete();
    }

    /*
    |--------------------------------------------------------------------------
    | Attach a user to a root node
    |--------------------------------------------------------------------------
    */
    public function attachUser(Rollup $rollup, User $user): void
    {
        if (!$rollup->isRoot()) {
            throw ValidationException::withMessages([
                'rollup_id' => 'Only root nodes can have users attached.',
            ]);
        }

        $rollup->users()->syncWithoutDetaching($user->id);
    }

    /*
    |--------------------------------------------------------------------------
    | Attach a category to a leaf node
    |--------------------------------------------------------------------------
    */
    public function attachCategory(Rollup $rollup, Category $category): void
    {
        if (!$rollup->isLeaf()) {
            throw ValidationException::withMessages([
                'rollup_id' => 'Only leaf nodes can have categories attached.',
            ]);
        }

        $rollup->categories()->syncWithoutDetaching($category->id);
    }
}
