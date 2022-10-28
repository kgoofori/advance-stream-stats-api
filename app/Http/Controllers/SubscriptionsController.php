<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Utils\BraintreeProvider;

class SubscriptionsController extends Controller
{
    public function getPlans()
    {
        $plans = (new BraintreeProvider)->gateway()->plan()->all();

        return response()->json([
            'plans' => $plans
        ]);
    }

    public function subscribe(Request $request)
    {
        $subscription = auth()->user()->subscribeToPlan($request->nonceFromTheClient, $request->planId);

        return response()->json([
            'message' => 'Subscription successful',
            'subscription' => $subscription
        ]);
    }

    public function cancelSubscription()
    {
        $subscription = auth()->user()->cancelSubscription();

        return response()->json([
            'message' => 'Subscription cancelled successfully',
            'subscription' => $subscription
        ]);
        
    }
}
