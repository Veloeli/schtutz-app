<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Category;

class ItemService
{
    public function visibleFor(User $user): Collection
    {
        // Load all items with their relationships
        $items = Item::with('document', 'category')->get();
dd('hi!');
        // Filter using the ItemPolicy
        return $items->filter(function ($item) use ($user) {
            return Gate::forUser($user)->allows('view', $item);
        });
    }

    public function createItem($user, array $data)
    {
        return Item::create([
            'name' => $data['name'],
            'amount' => $data['amount'],
            'category_id' => $data['category_id'] ?? null,
            'user_id' => $user->id,
        ]);
    }

    public function updateItem(Item $item, array $data)
    {
        return $item->update([
            'name' => $data['name'],
            'amount' => $data['amount'],
            'category_id' => $data['category_id'] ?? null,
        ]);
    }
}
