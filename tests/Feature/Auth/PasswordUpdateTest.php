<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_can_update_password_with_correct_current_password()
    {
        // Arrange: create a user
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        // Act: submit the password update form
        $response = $this->actingAs($user)->put('/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        // Assert: redirected to profile
        $response->assertRedirect(route('profile.edit'));

        // Assert: success message exists
        $response->assertSessionHas('status', 'password-updated');

        // Assert: password actually changed
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
    }

    #[Test]
    public function update_fails_if_current_password_is_wrong()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->from('/password/change')->put('/password', [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        // Assert: redirected back to form
        $response->assertRedirect('/password/change');

        // Assert: validation error exists
        $response->assertSessionHasErrors('current_password');

        // Assert: password NOT changed
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    #[Test]
    public function update_fails_if_password_confirmation_does_not_match()
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)->from('/password/change')->put('/password', [
            'current_password' => 'old-password',
            'password' => 'new-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect('/password/change');
        $response->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
