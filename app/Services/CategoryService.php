<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Models\Rollup;
use App\Models\Document;
use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CategoryService
{
    /**
     * Return all categories visible to the user.
     * Visibility is enforced by the Category global scope.
     */
    public function allVisible(User $user): Collection
    {
        // Global scope already filters visibility
        $categories = Category::select('categories.*')
            ->with(['team', 'owner'])
            ->orderBy('name')
            ->get();

        return $categories;
    }

    /**
     * Return all categories available to the user for a given document date
     * Visibility is enforced by the Category global scope.
     */
    public function visibleForDocument(User $user, Document $document): Collection
    {
        return $this->queryVisibleForDocument($user, $document);
    }

    private function queryVisibleForDocument(User $user, Document $document): Collection
    {
        $date = $document->posting_date;
        $ownerId = $document->owner_id;

        // Determine the root rollup for this user/team
        $rootId = $this->resolveRootRollup($user)->id;

        return Category::query()
            ->select([
                'categories.*',
                DB::raw('CONCAT(rp.path, " > ", categories.name) AS path_to_category'),
                'rp.depth',
                DB::raw("
                    CASE 
                        WHEN categories.team_id is not null THEN teams.name
                        WHEN categories.user_id = {$user->id} THEN null
                        ELSE users.name 
                    END AS source_label
                ")
            ])
            ->with(['team', 'owner'])

            // joins for users and teams
            ->leftJoin('users', 'categories.user_id', '=', 'users.id')
            ->leftJoin('teams', 'categories.team_id', '=', 'teams.id')
            ->leftJoin('team_user', function ($join) use ($ownerId) {
                $join->on('team_user.team_id', '=', 'teams.id')
                     ->where('team_user.user_id', '=', $ownerId);
            })

            // joins for rollup hierarchy
            ->leftJoin('rollup_category as rc', 'rc.category_id', '=', 'categories.id')
            ->leftJoin('rollup_paths_view as rp', 'rp.id', '=', 'rc.rollup_id')

            // restrict to the correct root tree
            ->where('rp.root_id', $rootId)

            // Existing rules
            ->where('categories.is_selectable', 1)

            // Team validity window
            ->where(function ($q) use ($date) {
                $q->whereNull('teams.id')
                  ->orWhereRaw('? between coalesce(teams.valid_from, "1900-01-01") 
                                          and coalesce(teams.valid_until, "2999-12-31")', [$date]);
            })

            // Membership validity window
            ->whereRaw('? between coalesce(team_user.member_from, "1900-01-01") 
                                and coalesce(team_user.member_to, "2999-12-31")', [$date])

            // Category ownership
            ->where(function ($q) use ($ownerId) {
                $q->where('categories.user_id', $ownerId)
                  ->orWhereNotNull('categories.team_id');
            })

            // Order by hierarchical path
            ->orderBy('path_to_category')

            ->get();
    }

    /**
     * Find the most frequently used category for a given search term (item name)
     * Visibility is enforced by the Category global scope.
     */
    public function suggestCategory(User $user, Document $document, string $itemName): ?int
    {
        if (strlen($itemName) < 3) {
            return null;
        }

        // Get allowed categories for this document
        $categoryIds = $this->visibleForDocument($user, $document)->pluck('id')->toArray();

        if (empty($categoryIds)) {
            return null;
        }

        // Find best historical match
        $categoryId = Item::query()
            ->select('category_id')
            ->whereIn('category_id', $categoryIds)
            ->whereRaw('MATCH(name) AGAINST (? IN NATURAL LANGUAGE MODE)', [$itemName])
            ->groupBy('category_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(1)
            ->value('category_id');

        return $categoryId;
    }

    protected function resolveRootRollup(User $user): ?Rollup
    {
        // 1. Session root_id (global scope ensures visibility)
        if ($rootId = session('root_id')) {
            if ($root = Rollup::find($rootId)) {
                return $root;
            }
        }

        // 2. User preference (global scope ensures visibility)
        if ($user->preferred_rollup) {
            return $user->preferred_rollup;
        }

        // 3. First visible rollup (global scope ensures visibility)
        if ($first = Rollup::first()) {
            return $first;
        }

        // 4. Nothing available
        return null;
    }

    public function create(User $user, array $data): Category
    {
        return Category::create([
            'name'          => $data['name'],
            'user_id'       => $user->id,
            'team_id'       => $data['team_id'] ?? null,
            'code'          => $data['code'] ?? null,
            'type'          => $data['type'],
        ]);
    }

    public function update(Category $category, array $data)
    {
        // Only update fields that are actually provided
        $allowed = [
            'name',
            'code',
            'type',
            'is_selectable',
        ];

        // If team_id is provided, allow assignment (your second-step workflow)
        if (array_key_exists('team_id', $data)) {
            $allowed[] = 'team_id';
        }

        $category->update(array_intersect_key($data, array_flip($allowed)));

        return $category;
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
