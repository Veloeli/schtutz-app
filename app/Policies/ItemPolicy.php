<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use App\Services\VisibilityService;

class ItemPolicy
{
    public function update(User $user, Item $item)
    {
        return VisibilityService::canEditItem($user, $item);
    }

    public function delete(User $user, Item $item)
    {
        return VisibilityService::canEditItem($user, $item);
    }
}
