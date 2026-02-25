<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
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
        return Category::select('categories.*')
            ->with(['team', 'owner'])
            ->orderBy('name')
            ->get();
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
