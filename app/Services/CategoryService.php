<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;

class CategoryService
{
    /**
     * Get all categories visible to a user: - 
     * - categories owned by the user
     * - categories belonging to the user's team
     * - categories owned by team members revealing all their categories
     */
    public function getCategoriesForUser(User $user)
    {
        // All team IDs the user CURRENTLY belongs to
        $teamIds = $user->activeTeams->pluck('id');

        // Team members who explicitly allow revealing private categories
        $teamMemberIds = User::whereHas('teams', function ($query) use ($teamIds) {
            $query->whereIn('teams.id', $teamIds)
                  ->where('team_user.reveal_private', true);
        })->pluck('id');

        return Category::query()
            ->where(function ($q) use ($user, $teamIds, $teamMemberIds) {

                //
                // (1) My non-team categories
                //
                $q->where(function ($q2) use ($user) {
                    $q2->where('user_id', $user->id)
                       ->whereNull('team_id');
                })

                //
                // (2) Categories belonging to my active teams
                //
                ->orWhereIn('team_id', $teamIds)

                //
                // (3) Non-team categories of team members who allow reveal
                //
                ->orWhere(function ($q2) use ($teamMemberIds) {
                    $q2->whereIn('user_id', $teamMemberIds)
                       ->whereNull('team_id');
                });
            })
            ->with(['parent', 'team'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function createCategory(User $user, array $data)
    {
        return Category::create([
            'name'          => $data['name'],
            'user_id'       => $user->id,
            'team_id'       => $data['team_id'] ?? null,
            'parent_id'     => $data['parent_id'] ?? null,
            'code'          => $data['code'] ?? null,
            'is_selectable' => isset($data['is_selectable']),
            'sort_order'    => $data['sort_order'] ?? null,
        ]);
    }

    public function updateCategory(Category $category, array $data)
    {
        $category->update([
            'name'          => $data['name'],
            'team_id'       => $data['team_id'] ?? $category->team_id,
            'parent_id'     => $data['parent_id'] ?? null,
            'team_id'       => $data['team_id'] ?? null,
            'code'          => $data['code'] ?? null,
            'is_selectable' => isset($data['is_selectable']),
            'sort_order'    => $data['sort_order'] ?? null,
        ]);

        return $category;
    }
}
