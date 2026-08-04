<?php

namespace Database\Factories;

use App\Models\Quote;
use App\Models\Security;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition()
    {
        return [
            'security_id' => Security::factory(),
            'quote_date'  => $this->faker->date(),
            'price'       => $this->faker->randomFloat(6, 0.000001, 5000),
            'created_at'  => now(),
            'updated_at'  => now(),
        ];
    }
}
