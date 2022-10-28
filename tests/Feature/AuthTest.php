<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    /**
     * Register user.
     *
     * @return void
     */
    public function test_user_can_register()
    {

        $response = $this->postJson('api/auth/register', [
            'first_name' => 'Gideon',
            'last_name' => 'Ofori',
            'email' => 'email@gmail.com',
            'password' => 'pass9999',
            'password_confirmation' => 'pass9999'
        ]);

        $response->assertStatus(200);

        // dd($response->json());
        $response->assertJson(function (AssertableJson $json){
            $json->hasAll(['access_token', 'token_type', 'expires_in']);
        });
        
        $this->assertDatabaseHas('users', ['first_name' => 'Gideon', 'last_name' => 'Ofori',  'email' => 'email@gmail.com',]);
    }

    public function test_registered_user_can_login()
    {
        $user = User::factory()->create(); //default password is password

        $response = $this->postJson('api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $response->assertStatus(200);

        $response->assertJson(function (AssertableJson $json){
            $json->hasAll(['access_token', 'token_type', 'expires_in']);
        });
    }
}
