<?php 
namespace App\Utils;

use Braintree\Gateway;

class BraintreeProvider{

    protected $gateway;

    public function __construct()
    {
        $this->gateway = new Gateway([
            'environment' => config('payments.braintree.environment'),
            'merchantId' => config('payments.braintree.merchantId'),
            'publicKey' => config('payments.braintree.publicKey'),
            'privateKey' => config('payments.braintree.privateKey'),
        ]);
    }

    public function gateway()
    {
        return $this->gateway;
    }
    
}