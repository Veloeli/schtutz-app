<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use App\Models\Rollup;
use Illuminate\Support\Collection;

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

        $paths = $this->buildPathsFromRollup($root);

        return $categories->map(function ($category) use ($paths) {
            $category->full_path = $paths[$category->id] ?? $category->name;
            return $category;
        });
    }

    protected function buildPathsFromRollup(Rollup $node, string $prefix = ''): array
    {
        $current = trim($prefix . ' / ' . $node->name, ' /');

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
