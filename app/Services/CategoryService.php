<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategoryService
{
    public function getCategoriesForUser(User $user)
    {
        return Category::query()
            ->where('user_id', $user->id)
            ->orWhereHas('subscribers', fn($q) => $q->where('user_id', $user->id))
            ->get();
    }

    public function createCategory(User $user, array $data)
    {
        return Category::create([
            'name'       => $data['name'],
            'is_private' => isset($data['is_private']) ? 1 : 0,
            'user_id'    => $user->id,
        ]);
    }
}
