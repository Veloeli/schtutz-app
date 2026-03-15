<?php

namespace Database\Factories;

use App\Models\Security;
use App\Models\User;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

class SecurityFactory extends Factory
{
    protected $model = Security::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->company . ' Security',
            'ticker'      => strtoupper($this->faker->lexify('???')),
            'isin'        => strtoupper($this->faker->bothify('??##########')),
            'identifier'  => strtoupper($this->faker->bothify('ID########')),
            'company'     => $this->faker->company,
            'asset_class' => $this->faker->randomElement(array_keys(Security::ASSET_CLASSES)),
            'currency_id' => null, // optional
            'region'      => $this->faker->randomElement(['EU', 'US', 'ASIA']),
            'strategy'    => $this->faker->word,
            'theme'       => $this->faker->word,
            'is_in_use'   => 1,
            'is_tracked'  => 0,
            'is_hedged'   => 0,

            // Option fields
            'option_type'        => null,
            'option_strike'      => null,
            'option_multiplier'  => null,
            'option_underlying_id' => null,
            'option_calculate'   => 0,

            // Ownership
            'team_id' => Team::factory(),   // overridden in tests
            'user_id' => User::factory(),   // overridden in tests
        ];
    }
}
