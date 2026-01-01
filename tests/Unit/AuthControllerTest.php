<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\StripeServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Mockery;
use PHPUnit\Framework\TestCase;

class AuthControllerTest extends TestCase
{
    public function test_email_verification_with_valid_token(): void
    {
        // Mock Crypt facade
        Crypt::shouldReceive('decrypt')
            ->once()
            ->with('encrypted_token')
            ->andReturn('user@example.com');

        // Create a mock user
        $user = User::factory()->make([
            'email' => 'user@example.com',
            'email_verified_at' => null,
            'is_active' => 0,
        ]);

        // Mock User model
        User::shouldReceive('where')
            ->once()
            ->with('email', 'user@example.com')
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($user);

        // Mock StripeServices
        $mockStripe = Mockery::mock('overload:App\Services\StripeServices');
        $mockStripe->shouldReceive('createStandardConnectAccount')
            ->once()
            ->with($user)
            ->andReturn((object) ['id' => 'acct_123456789']);

        $mockStripe->shouldReceive('createConnectAccountLink')
            ->once()
            ->with('acct_123456789', 'https://teekit.com', 'https://teekit.com')
            ->andReturn((object) ['url' => 'https://stripe.com/onboarding']);

        // Mock Mail facade
        Mail::fake();

        // Create request
        $request = new Request(['token' => 'encrypted_token']);

        // Call the method
        $response = $this->app->make('App\Http\Controllers\VerificationController')
            ->verify($request);

        // Assertions
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Account successfully verified', $response->getContent());

        // Verify email was sent
        Mail::assertSent(StripeConnectedAccMail::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    public function test_email_verification_with_invalid_token(): void
    {
        Crypt::shouldReceive('decrypt')
            ->once()
            ->with('invalid_token')
            ->andReturn('invalid@example.com');

        User::shouldReceive('where')
            ->once()
            ->with('email', 'invalid@example.com')
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn(null);

        $request = new Request(['token' => 'invalid_token']);

        $response = $this->app->make('App\Http\Controllers\VerificationController')
            ->verify($request);

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_already_verified_account(): void
    {
        Crypt::shouldReceive('decrypt')
            ->once()
            ->with('encrypted_token')
            ->andReturn('user@example.com');

        $user = User::factory()->make([
            'email' => 'user@example.com',
            'email_verified_at' => now(),
            'is_active' => 1,
        ]);

        User::shouldReceive('where')
            ->once()
            ->with('email', 'user@example.com')
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($user);

        $request = new Request(['token' => 'encrypted_token']);

        $response = $this->app->make('App\Http\Controllers\VerificationController')
            ->verify($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Account already verified', $response->getContent());
    }

    public function test_email_verification_endpoint(): void
    {
        Mail::fake();

        $user = User::factory()->create([
            'email_verified_at' => null,
            'is_active' => 0,
        ]);

        $encryptedToken = Crypt::encrypt($user->email);

        $response = $this->post('/verify', [
            'token' => $encryptedToken,
        ]);

        $response->assertStatus(200)
            ->assertSee('Account successfully verified');

        $this->assertNotNull($user->fresh()->email_verified_at);
        $this->assertEquals(1, $user->fresh()->is_active);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
