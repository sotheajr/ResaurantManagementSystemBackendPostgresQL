<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    public function test_user_can_login_and_receive_token(): void
    {
        $response = $this->postJson('/api/login', [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'message',
                     'data' => [
                         'user',
                         'token',
                     ],
                 ]);

        $this->assertNotEmpty($response->json('data.token'));
    }
}
