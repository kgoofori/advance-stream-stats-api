<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsUserSubscribedMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->isSubscribed()){
            return $next($request);
        }

        return response()->json([
            [
                'message' => 'User is not subscribed',
                'user' => auth()->user()
            ]

        ], 401);
    }
}
