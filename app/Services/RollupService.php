<?php

namespace App\Services;

use App\Models\Rollup;
use App\Models\Category;
use App\Models\User;
use App\Services\UserCacheVersionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RollupService
{
    public function __construct(
        protected UserCacheVersionService $versionService
    ) {}

    protected function incrementUserVersion(): void
    {
        $this->versionService->increment('categories');
    }

    /*
    |--------------------------------------------------------------------------
    | Create a root node
    |--------------------------------------------------------------------------
    */
    public function createRoot(array $data, User $creator): Rollup
    {
        //invalidate cache
        $this->incrementUserVersion();

        return DB::transaction(function () use ($data, $creator) {

            $rollup = Rollup::create([
                'name'      => $data['name'],
                'code'      => $data['code'] ?? null,
                'parent_id' => null,
                'user_id'   => $creator->id,
                'team_id'   => $data['team_id'] ?? null,
            ]);

            return $rollup;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Create a child node
    |--------------------------------------------------------------------------
    */
    public function createChild(Rollup $parent, array $data, User $creator): Rollup
    {
        if (!$parent->exists) {
            throw ValidationException::withMessages([
                'parent_id' => 'Parent rollup does not exist.',
            ]);
        }

        //invalidate cache
        $this->incrementUserVersion();

        return Rollup::create([
            'name'      => $data['name'],
            'code'      => $data['code'] ?? null,
            'parent_id' => $parent->id,
            'user_id'   => $parent->user_id // all nodes assigned to same user, even if someone else creates node
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
            'name'    => $data['name'],
            'code'    => $data['code'] ?? null,
            'user_id' => $data['user_id'],
            'team_id' => $data['team_id'] ?? null,
        ]);

        //invalidate cache
        $this->incrementUserVersion();

        return $rollup;
    }

    /*
    |--------------------------------------------------------------------------
    | Delete a rollup node
    |--------------------------------------------------------------------------
    */
    public function delete(Rollup $rollup)
    {
        DB::transaction(function () use ($rollup) {
            
            // 1. Remove as user preferred hierarchy
            User::where('preferred_root_id', $rollup->id)->update([
                'preferred_root_id' => null,
            ]);

            // 2. Remove assigned categories (if any) - usually nothing
            $rollup->categories()->detach();

            // 3. Remove children (recursive) - usually nothing
            foreach ($rollup->children as $child) {
                $this->delete($child);
            }

            // 4. Delete hierarchy
            $rollup->delete();
        });

        //invalidate cache
        $this->incrementUserVersion();
    }

    /*
    |--------------------------------------------------------------------------
    | Attach a category to a node
    |--------------------------------------------------------------------------
    */
    public function attachCategory(Rollup $rollup, Category $category): void
    {
        $rollup->categories()->syncWithoutDetaching($category->id);

        //invalidate cache
        $this->incrementUserVersion();
    }
    
    public function detachCategory(Rollup $rollup, Category $category): void
    {
        $rollup->categories()->detach($category->id);

        // invalidate cache
        $this->incrementUserVersion();
    }

}
