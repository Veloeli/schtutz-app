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

    public function update(Category $category, array $data): Category
    {
        $category->update([
            'name'          => $data['name'],
            'team_id'       => $data['team_id'] === '' ? null : $data['team_id'],
            'code'          => $data['code'] ?? null,
            'type'          => $data['type'],
            'is_selectable' => isset($data['is_selectable']),
        ]);

        return $category;
    }

}
