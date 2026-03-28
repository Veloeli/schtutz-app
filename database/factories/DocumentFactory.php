<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DocumentFactory extends Factory
{
    protected $model = Document::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence(3),
            'posting_date' => now()->toDateString(),
            'repeat_pattern' => null,
            'repeat_constant' => false,
            'user_id' => User::factory(),
        ];
    }
}
