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

        $response = $user->subscribeToPlan(
            'fake-valid-nonce',
            'advanced_stream_stats_monthly'
        );

        $this->assertTrue($response->success);
        $this->assertTrue($user->fresh()->isSubscribed());
    }

    public function test_user_can_fetch_subscription()
    {
        $user = User::factory()->create();

        $response = $user->subscribeToPlan(
            'fake-valid-nonce',
            'advanced_stream_stats_monthly'
        );

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

        $user->subscribeToPlan(
            'fake-valid-nonce',
            'advanced_stream_stats_monthly'
        );

        $freshUser = $user->fresh();

        $response = $freshUser->cancelSubscription();

        $this->assertTrue($response->success);

        $this->assertTrue($freshUser->isSubscribed());
        $this->assertEquals($response->subscription->status, \Braintree\Subscription::CANCELED);

    }
}
