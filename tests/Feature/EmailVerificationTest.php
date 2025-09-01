<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

class EmailVerificationTest extends TestCase
{
    public function test_verification_endpoint()
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'is_active' => 0
        ]);

        $encryptedToken = Crypt::encrypt($user->email);

        $response = $this->post('/verify', [
            'token' => $encryptedToken
        ]);

        $response->assertStatus(200)
            ->assertSee('Account successfully verified');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertEquals(1, $user->fresh()->is_active);
    }
}
