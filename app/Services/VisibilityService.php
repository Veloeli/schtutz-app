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
        $userId = $user->id;

        return cache()->remember(
            "allowed_users_{$userId}",
            now()->addSeconds(3),
            function () use ($userId) {
                return collect([$userId])->merge(
                    DB::table('team_user as other')
                        ->join('team_user as myself', 'myself.team_id', '=', 'other.team_id')
                        ->where('myself.user_id', $userId)
                        ->where('other.reveal_private', 1)
                        ->where('other.user_id', '!=', $userId)
                        ->pluck('other.user_id')
                );
            }
        )->map(fn($id) => (int) $id);
    }

    public static function allowedTeamIds(User $user)
    {
        return cache()->remember(
            "allowed_teams_{$user->id}",
            now()->addSeconds(3),
            function () use ($user) {
                return DB::table('team_user')
                    ->where('user_id', $user->id)
                    ->pluck('team_id')
                    ->unique()
                    ->values();
            }
        );
    }

    public static function canEditDocument(User $user, Document $document)
    {
        if ($document->wizard) {
            return false;
        }

        if (self::isFrozen($document)) {
            return false;
        }

        return self::allowedUserIds($user)->contains($document->owner_id);
    }

    public static function canEditItem(User $user, Item $item)
    {
        if ($item->document->wizard) {
            return false;
        }

        if (self::isFrozen($item->document)) {
            return false;
        }

        return self::allowedUserIds($user)->contains($item->document->owner_id);
    }

    protected static function isFrozen(Document $document): bool
    {
        if (!$document->owner->freeze_after) {
            return false; // no freeze rule for this user
        }

        $freezeDate = now()->subMonths($document->owner->freeze_after);

        return $document->posting_date < $freezeDate;
    }
}
