<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithoutDeprecationHandlingTrait;
use Throwable;

class LoginTest extends TestCase
{
    use RefreshDatabase;
    use WithoutDeprecationHandlingTrait;

    /**
     * @return void
     */
    public function testCannotLoginWithoutRequiredFields(): void
    {
        $response = $this->postJson(route('auth.login'));
        $response->assertStatus(422);
        //$response->assertInvalid(['password', 'email']);

        $response->assertJsonFragment([
            'success' => false,
            'message' => 'The given data was invalid.',
            'data' => [
                'errors' => [
                    'The email field is required. (and 1 more error)',
                ],
            ],
        ]);
    }

    /**
     * @return void
     */
    public function testCannotLoginWithWrongPassword(): void
    {
        $user = AuthTestHelper::mockUser();
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'incorrect',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The given data was invalid.',
            'data' => [
                'errors' => [
                    'The provided credentials are incorrect.'
                ],
            ],
        ]);

        AuthTestHelper::clearUser($user);
    }

    /**
     * @return void
     */
    public function testCannotLoginWithWrongEmail(): void
    {
        $response = $this->postJson(route('auth.login'), [
            'email' => 'unexists@mail.example',
            'password' => 'incorrect',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The given data was invalid.',
            "data" => [
                "errors" => [
                    "The selected email is invalid."
                ]
            ]
        ]);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testCanLogin(): void
    {
        $user = AuthTestHelper::mockUser();
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertCookieNotExpired(
            'refreshToken'
        );

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => AuthTestHelper::$loginSuccessBody
        ]);

        $accessToken = $response->decodeResponseJson()['data']['accessToken'];
        $this->assertTrue(AuthTestHelper::verifyAccessToken($accessToken));

        AuthTestHelper::clearUser($user);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testCanRefreshToken(): void
    {
        $user = AuthTestHelper::mockUser();
        $tokens = AuthTestHelper::generateTokens($user);

        // Manually make access token expired
        $moveTime = config('sanctum.expiration') + 5;
        $this->travel($moveTime)->minutes();
        $this->assertFalse(AuthTestHelper::verifyAccessToken($tokens['accessToken']));

        $response = $this
            ->withUnencryptedCookie('refreshToken', $tokens['refreshToken'])
            ->withCredentials()
            ->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(route('auth.refresh'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'accessToken'
            ]
        ]);

        $accessToken = $response->decodeResponseJson()['data']['accessToken'];
        $this->assertTrue(AuthTestHelper::verifyAccessToken($accessToken));

        AuthTestHelper::clearUser($user);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function testCanRefreshTokenAfterLogin(): void
    {
        $user = AuthTestHelper::mockUser();
        $response = $this->postJson(route('auth.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);

        $response->assertCookieNotExpired('refreshToken');
        $refreshToken = $response->getCookie('refreshToken', false)->getValue();
        $accessToken = $response->decodeResponseJson()['data']['accessToken'];

        // Manually make access token expired
        $moveTime = config('sanctum.expiration') + 5;
        $this->travel($moveTime)->minutes();
        $this->assertFalse(AuthTestHelper::verifyAccessToken($accessToken));

        $response = $this
            ->withUnencryptedCookie('refreshToken', $refreshToken)
            ->withCredentials()
            ->withHeader('Authorization', 'Bearer ' . $refreshToken)
            ->postJson(route('auth.refresh'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'accessToken'
            ]
        ]);

        $accessToken = $response->decodeResponseJson()['data']['accessToken'];
        $this->assertTrue(AuthTestHelper::verifyAccessToken($accessToken));

        AuthTestHelper::clearUser($user);
    }

    /**
     * @return void
     */
    public function testAccessTokenExpiration(): void
    {
        $user = AuthTestHelper::mockUser(true);
        $tokens = AuthTestHelper::generateTokens($user);

        // Manually make access token expired
        $this->travel(config('sanctum.expiration') + 10)->minutes();

        $response = $this->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(route('auth.me'));

        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Authentication is required to access this resource.',
        ]);

        AuthTestHelper::clearUser($user);
    }

    /**
     * @return void
     */
    public function testRefreshTokenExpiration(): void
    {
        $user = AuthTestHelper::mockUser();
        $tokens = AuthTestHelper::generateTokens($user);

        // Manually make access token expired
        $this->travel(config('sanctum.rt_expiration') + 5)->minutes();

        $response = $this
            ->withCredentials()
            ->withUnencryptedCookie('refreshToken', $tokens['refreshToken'])
            ->withHeader('Authorization', 'Bearer ' . $tokens['accessToken'])
            ->postJson(route('auth.refresh'));


        $response->assertStatus(401);
        $response->assertJson([
            'message' => 'Authentication is required to access this resource.',
        ]);

        AuthTestHelper::clearUser($user);
    }
}
