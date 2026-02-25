<?php

namespace Tests\Feature\Profile;

use PHPUnit\Framework\Attributes\Test;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DeputyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_add_a_deputy()
    {
        $owner = User::factory()->create();
        $deputy = User::factory()->create();

    $response = $this
        ->actingAs($owner)
        ->from('/profile')
        ->post('/profile/deputies', [
            'email' => $deputy->email,
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

        // Reload owner to check relationship
        $owner->refresh();

        $this->assertTrue(
            $owner->deputies->contains($deputy),
            'Deputy should be attached to owner'
        );
    }

    #[Test]
    public function user_can_remove_a_deputy()
    {
        $owner = User::factory()->create();
        $deputy = User::factory()->create();

        // Pre‑attach deputy
        $owner->deputies()->attach($deputy->id);

        $response = $this
            ->actingAs($owner)
            ->from('/profile')
            ->delete("/profile/deputies/{$deputy->id}");

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $owner->refresh();

        $this->assertFalse(
            $owner->deputies->contains($deputy),
            'Deputy should be detached from owner'
        );
    }
}
