<?php

namespace Tests\Feature\Auth;

use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    /**
     * @return void
     */
    public function testCanLogout(): void
    {
        $user = AuthTestHelper::mockUser();
        $tokens = AuthTestHelper::generateTokens($user);

        $response = $this
            ->actingAs($user, 'sanctum')
            ->withUnencryptedCookie('refreshToken', $tokens['refreshToken'])
            ->withCredentials()
            ->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(route('auth.logout'));
        $response->assertStatus(200);

        $response->assertCookie('refreshToken', '', false);
        $this->assertEquals(0, PersonalAccessToken::where('tokenable_id', $user->id)->count());

        $response->assertJson([
            'success' => true,
            'message' => 'You are successfully logged out.',
            'data' => []
        ]);

        AuthTestHelper::clearUser($user);
    }

    /**
     * @return void
     */
    public function testCanLogoutWithExpiredSession(): void
    {
        $response = $this->postJson(route('auth.logout'));

        $response->assertStatus(204);

        $response->assertCookie('refreshToken');

        $response->assertJson([
            'status' => true,
            'message' => 'Successfully logged out.',
            'data' => []
        ]);
    }
}
