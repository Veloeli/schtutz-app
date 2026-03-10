<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Models\Rollup;
use App\Models\Document;
use App\Models\Item;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

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

        return $this->attachFullPaths($categories, $user);
    }

    public function visibleForDocument(User $user, Document $document): Collection
    {
        $date = $document->posting_date->format('Y-m-d');

        $cacheKey = "categories:visible:user:{$user->id}:date:{$date}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $document) {
            return $this->queryVisibleForDocument($user, $document);
        });
    }

    private function queryVisibleForDocument(User $user, Document $document): Collection
    {
        $date = $document->posting_date;
        $ownerId = $document->owner_id;

        $categories = Category::query()
            ->select('categories.*')
            ->with(['team', 'owner'])
            ->leftJoin('teams', 'categories.team_id', '=', 'teams.id')
            ->leftJoin('team_user', function ($join) use ($ownerId) {
                $join->on('team_user.team_id', '=', 'teams.id')
                     ->where('team_user.user_id', '=', $ownerId);
            })
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

            ->orderBy('categories.name')
            ->get();

        return $this->attachFullPaths($categories, $user);
    }

    public function suggestCategory(User $user, Document $document, string $itemName): ?int
    {
        if (strlen($itemName) < 3) {
            return null;
        }

        // Get allowed categories for this document
        $allowed = $this->visibleForDocument($user, $document)->pluck('id')->toArray();

        if (empty($allowed)) {
            return null;
        }

        // Find best historical match
        return Item::query()
            ->select('category_id')
            ->whereIn('category_id', $allowed)
            ->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($itemName) . '%'])
            ->groupBy('category_id')
            ->orderByRaw('COUNT(*) DESC')   // most common match wins
            ->limit(1)
            ->value('category_id');
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

    public function attachFullPaths(Collection $categories, User $user): Collection
    {
        $root = $this->resolveRootRollup($user);

        if (! $root instanceof Rollup) {
            return $categories; // fallback
        }

        $cacheKey = "categories.paths.user.{$user->id}.root.{$root->id}";

        $paths = cache()->remember($cacheKey, now()->addMinutes(10), function () use ($root) {
            return $this->buildPathsFromRollup($root);
        });

        return $categories->map(function ($cat) use ($paths, $user) { 
            $cat->full_path = $paths[$cat->id] ?? $cat->name; 
            $cat->source_label = $this->computeSourceLabel($cat, $user); 
            return $cat; 
        });    
    }

    protected function buildPathsFromRollup(Rollup $node, string $prefix = ''): array
    {
        // skip root node
        $current = ($node->parent_id) 
            ? trim($prefix . ' / ' . $node->name, ' /') 
            : '';

        $paths = [];

        // categories attached to this rollup
        foreach ($node->categories as $cat) {
            $paths[$cat->id] = $current . ' / ' . $cat->name;
        }

        // recurse into children
        foreach ($node->children as $child) {
            $paths += $this->buildPathsFromRollup($child, $current);
        }

        return $paths;
    }

    protected function computeSourceLabel(Category $category, User $user): ?string
    {
        // Case 1: Category belongs to a team
        if ($category->team) {
            return $category->team->name;
        }

        // Case 2: Category belongs to another user
        if ($category->owner && $category->owner->id !== $user->id) {
            return $category->owner->name;
        }

        // Case 3: Category belongs to the current user → no label
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
            'is_selectable' => isset($data['is_selectable']),
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
}
