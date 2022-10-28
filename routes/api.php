<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\SubscriptionsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'

], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
});

Route::middleware(['auth:api'])->group(function () {
    Route::get('/trial-stats', [StatsController::class, 'trialStats']);

    Route::get('/subscription/plans', [SubscriptionsController::class, 'getPlans']);
    Route::post('/subscription/subscribe', [SubscriptionsController::class, 'subscribe']);
    Route::post('/subscription/cancel', [SubscriptionsController::class, 'cancelSubscription']);
});

Route::middleware(['auth:api', 'subscribed'])->group(function () {
    Route::get('/advance-stats', [StatsController::class, 'advanceStats']);
});