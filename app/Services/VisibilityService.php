<?php

namespace App\Services;

use App\Models\User;
use App\Models\Document;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class VisibilityService
{
    public static function allowedUserIds(User $user)
    {
        return cache()->remember(
            "allowed_users_{$user->id}",
            now()->addSeconds(1),
            function () use ($user) {

                // IMPORTANT: raw DB query, no Eloquent relationships
                $represented = DB::table('deputies')
                    ->where('deputy_user_id', $user->id)
                    ->pluck('user_id');

                return collect([$user->id])
                    ->merge($represented)
                    ->unique()
                    ->values();
            }
        );
    }

    public static function allowedTeamIds(User $user)
    {
        return cache()->remember(
            "allowed_teams_{$user->id}",
            now()->addSeconds(1),
            function () use ($user) {

                // IMPORTANT: raw DB query, no Eloquent relationships
                return DB::table('team_user')
                    ->whereIn('user_id', self::allowedUserIds($user))
                    ->pluck('team_id')
                    ->unique()
                    ->values();
            }
        );
    }

    public static function itemVisible(User $user, Item $item)
    {
        $allowedUsers = self::allowedUserIds($user);
        $allowedTeams = self::allowedTeamIds($user);

        return $allowedUsers->contains($item->category->owner_id)
            || $allowedTeams->contains($item->category->team_id);
    }

    public static function documentVisible(User $user, $document)
    {
        $allowedUsers = self::allowedUserIds($user);

        if ($allowedUsers->contains($document->owner_id)) {
            return true;
        }

        return $document->items()->where(function ($q) use ($user) {
            $allowedUsers = self::allowedUserIds($user);
            $allowedTeams = self::allowedTeamIds($user);

            $q->whereHas('document', function ($q) use ($allowedUsers) {
                $q->whereIn('owner_id', $allowedUsers);
            });

            $q->orWhereHas('category', function ($q) use ($allowedTeams) {
                $q->whereIn('team_id', $allowedTeams);
            });
        })->exists();
    }

    public static function categoryVisible(User $user, $category)
    {

        return false;
    }

    public static function teamVisible(User $user, $team)
    {
        $allowedTeams = self::allowedTeamIds($user);

        return $allowedTeams->contains($team->id);
    }

    public static function teamMemberVisible(User $user, $teamUser)
    {
        $allowedTeams = self::allowedTeamIds($user);

        return $allowedTeams->contains($teamUser->team_id);
    }
    
    public static function canEditDocument(User $user, Document $document)
    {
        return self::allowedUserIds($user)->contains($document->owner_id);
    }

    public static function canEditItem(User $user, Item $item)
    {
        $allowedUsers = self::allowedUserIds($user);

        return $allowedUsers->contains($item->document->owner_id);
    }
}
