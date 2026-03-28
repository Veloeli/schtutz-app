<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;

class ItemPolicy
{
    public function update(User $user, Item $item)
    {
        return $user->can('update', $item->document);
    }

    public function delete(User $user, Item $item)
    {
        return $user->can('update', $item->document);
    }
}
