<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Category;

class ItemService
{
    public function getItemsForUser($user)
    {
        $items = Item::query()->get();

        $ownedPrivate = Category::where('is_private', true)
            ->where('user_id', $user->id)
            ->get();

        $sharedPrivate = $user->categories()
            ->where('is_private', true)
            ->get();

        $publicSubscribed = $user->categories()
            ->where('is_private', false)
            ->get();

        $categories = $ownedPrivate
            ->merge($sharedPrivate)
            ->merge($publicSubscribed)
            ->unique('id');

        return [
            'items' => $items,
            'categories' => $categories,
        ];
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
