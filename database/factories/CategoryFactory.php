<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition()
    {
        return [
            'name'          => $this->faker->words(2, true),
            'code'          => strtoupper($this->faker->bothify('CAT###')),
            'user_id'       => User::factory(),   // creator
            'team_id'       => null,              // default: private category
            'is_selectable' => true,
            'type'          => $this->faker->randomElement(array_keys(Category::TYPES)),
        ];
    }

    /**
     * Category belongs to a new team.
     */
    public function withTeam()
    {
        return $this->state(fn () => [
            'team_id' => Team::factory(),
        ]);
    }

    /**
     * Category belongs to a specific team.
     */
    public function forTeam(Team $team)
    {
        return $this->state(fn () => [
            'team_id' => $team->id,
        ]);
    }

    /**
     * Category created by a specific user.
     */
    public function forUser(User $user)
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Category is not selectable.
     */
    public function notSelectable()
    {
        return $this->state(fn () => [
            'is_selectable' => false,
        ]);
    }

    /**
     * Category with a specific type.
     */
    public function type(string $type)
    {
        return $this->state(fn () => [
            'type' => $type,
        ]);
    }
}
