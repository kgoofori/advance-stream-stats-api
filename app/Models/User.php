<?php

namespace App\Models;

use Carbon\Carbon;
use App\Utils\BraintreeProvider;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'braintree_customer_id',
        'braintree_subscription_id',
        'braintree_plan_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];



    /**
     * Create user account on braintree
     *
     * @return Braintree\Customer | false
     */
    public function braintreeAccount()
    {
        $customerGateway = (new BraintreeProvider)->gateway()->customer();

        if($this->braintree_customer_id){
            return $customerGateway->find($this->braintree_customer_id);
        }

        $response =  $customerGateway->create([
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
        ]);

        if(!$response->success){
            return false;
        }

        $this->update(['braintree_customer_id' => $response->customer->id]);

        return $response->customer;
    }

    /**
     * Create user account on braintree
     * @param string $nonceFromTheClient
     * @param string $planId
     * 
     * @return Braintree\Result
     */
    public function subscribeToPlan($nonceFromTheClient, $planId)
    {

        $gateway = (new BraintreeProvider)->gateway();

        $payment = $gateway->paymentMethod()->create([
            'customerId' => $this->braintreeAccount()->id,
            'paymentMethodNonce' => $nonceFromTheClient
        ]);

        // dd($payment);

        if($payment->success){
            $response = $gateway->subscription()->create([
                'paymentMethodToken' => $payment->paymentMethod->token,
                'planId' => $planId,
            ]);

            if($response->success){
                $this->update([
                    'braintree_subscription_id' => $response->subscription->id,
                    'braintree_plan_id' => $response->subscription->id,
                ]);
            }

            return $response;
        }

        return $payment;
        
    }

    /**
     * Create user account on braintree
     *
     * @return Braintree\Subscription | false
     */
    public function subscription()
    {

        if($this->braintree_subscription_id){
            return (new BraintreeProvider)->gateway()->subscription()->find($this->braintree_subscription_id);
        }

        return false;
    }

    /**
     * Get user subscription
     *
     * @return Braintree\Subscription | false
     */
    public function cancelSubscription()
    {

        if($this->braintree_subscription_id){
            return (new BraintreeProvider)->gateway()->subscription()->cancel($this->braintree_subscription_id);
        }

        return false;
    }

    /**
     * Check if user user has active subscription
     *
     * @return boolean
     */

    public function isSubscribed()
    {
        $subscription = $this->subscription();

        if($subscription !== false){
            return now()->lt(Carbon::parse($subscription->paidThroughDate));
        }

        return false;
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }
}
