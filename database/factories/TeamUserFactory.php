<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Team;
use App\Models\Category;
use App\Models\TeamUser;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamUserFactory extends Factory
{
    protected $model = TeamUser::class;

    public function definition(): array
    {
        return [
            'reveal_private' => 0,
            'user_id'        => User::factory(),
            'team_id'        => Team::factory(),

            // Category gets the SAME user_id as the TeamUser
            'clearing_account' => function (array $attributes) {
                return Category::factory()->create([
                    'type'    => 'CL',
                    'user_id' => $attributes['user_id'],
                ])->id;
            },
        ];
    }
}
