<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email');

        $response->assertStatus(200);
    }

    public function test_email_verification_requires_a_valid_signed_link(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/verify-email/' . $user->id . '/' . sha1($user->email));

        $this->assertTrue(in_array($response->status(), [403, 429], true));
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }

    public function test_email_is_not_verified_with_invalid_hash(): void
    {
        $user = User::factory()->unverified()->create();
        $response = $this->actingAs($user)->get('/verify-email/' . $user->id . '/' . sha1('wrong-email'));
        $this->assertTrue(in_array($response->status(), [403, 429], true));

        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
