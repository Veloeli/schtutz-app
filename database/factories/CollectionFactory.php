<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class CollectionFactory extends Factory
{
    protected $model = List::class;

    public function definition()
    {
        $dateFrom = $this->faker->dateTimeBetween('-1 month', 'now');
        $dateTo   = (clone $dateFrom)->modify('+'.rand(1, 14).' days');

        return [
            'user_id'   => User::factory(),
            'team_id'   => Team::factory(),
            'name'      => $this->faker->sentence(3),
            'date_from' => $dateFrom->format('Y-m-d'),
            'date_to'   => $dateTo->format('Y-m-d'),
        ];
    }
}
