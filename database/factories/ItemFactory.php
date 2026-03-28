<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Document;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    protected $model = Item::class;

    public function definition(): array
    {
        return [
            'name'        => $this->faker->words(2, true),
            'amount'      => $this->faker->randomFloat(2, 1, 5000),
            'quantity'    => $this->faker->numberBetween(1, 20),
            'category_id' => Category::factory(),
            'document_id' => Document::factory(),
        ];
    }
}
