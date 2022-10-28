<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_create_user_on_braintree()
    {
        $user = User::factory()->create();

        $response = $user->braintreeAccount();

        $this->assertNotFalse($response);
    }

    public function test_user_can_subscribe_to_plan()
    {
        $user = User::factory()->create();

        $response = $this->subscribe($user);

        $this->assertTrue($response->success);
        $this->assertTrue($user->fresh()->isSubscribed());
    }

    public function test_user_can_fetch_subscription()
    {
        $user = User::factory()->create();

        $response = $this->subscribe($user);

        $freshUser = $user->fresh();

        $subscription = $freshUser->subscription();

        $this->assertNotFalse($subscription);

        $this->assertTrue($freshUser->isSubscribed());

        $this->assertEquals($response->subscription->id, $subscription->id);
    }

    public function test_user_can_cancel_subscription()
    {
        $user = User::factory()->create();
        $this->assertFalse($user->isSubscribed());

        $this->subscribe($user);

        $freshUser = $user->fresh();

        $response = $freshUser->cancelSubscription();

        $this->assertTrue($response->success);

        $this->assertTrue($freshUser->isSubscribed());
        $this->assertEquals($response->subscription->status, \Braintree\Subscription::CANCELED);

    }

    public function test_unsubscribed_users_cannot_view_advance_stats()
    {
        $user = User::factory()->create(); //default password is password

        $login = $this->postJson('api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ])->json();

        $response = $this->withToken($login['access_token'])->getJson('/api/trial-stats');
        $response->assertOk();

        $response = $this->withToken($login['access_token'])->getJson('/api/advance-stats');
        $response->assertUnauthorized();
        
    }

    public function test_subscribed_users_can_view_advance_stats()
    {
        $user = User::factory()->create(); //default password is password

        $this->subscribe($user);

        $this->assertTrue($user->fresh()->isSubscribed());

        $login = $this->postJson('api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ])->json();


        $response = $this->withToken($login['access_token'])->getJson('/api/trial-stats');
        $response->assertOk();

        $response = $this->withToken($login['access_token'])->getJson('/api/advance-stats');
        $response->assertOk();
        
    }

    protected function subscribe($user)
    {
        return $user->subscribeToPlan(
            'fake-valid-nonce',
            'advanced_stream_stats_monthly'
        );
    }
}
