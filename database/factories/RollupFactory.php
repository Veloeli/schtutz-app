<?php

namespace Database\Factories;

use App\Models\Rollup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RollupFactory extends Factory
{
    protected $model = Rollup::class;

    public function definition()
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'parent_id' => null, // root by default
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Rollup $rollup) {
            // mirror createRoot() behavior
            $rollup->users()->attach($rollup->user_id);
        });
    }

    /**
     * Create a child rollup with a given parent.
     */
    public function childOf(Rollup $parent)
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Rollup created by a specific user.
     */
    public function forUser(User $user)
    {
        return $this->state(fn () => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a hierarchy of N levels (root → child → grandchild…)
     */
    public function withDepth(int $levels = 2)
    {
        return $this->afterCreating(function (Rollup $root) use ($levels) {
            $current = $root;

            for ($i = 1; $i < $levels; $i++) {
                $current = Rollup::factory()->create([
                    'parent_id' => $current->id,
                ]);
            }
        });
    }

    /**
     * Create a root with X children.
     */
    public function withChildren(int $count = 1)
    {
        return $this->afterCreating(function (Rollup $root) use ($count) {
            Rollup::factory()
                ->count($count)
                ->create(['parent_id' => $root->id]);
        });
    }
}
