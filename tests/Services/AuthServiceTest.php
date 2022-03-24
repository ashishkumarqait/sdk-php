<?php

namespace Tests\Services;

use LiveIntent\User;
use LiveIntent\Services\TokenService;

class AuthServiceTest extends ServiceTestCase
{
    protected $serviceKey = 'auth';

    public function testGetCurrentUserWithAccessToken()
    {
        $tokenService = new TokenService([
            'client_id' => env('CLIENT_ID'),
            'client_secret' => env('CLIENT_SECRET'),
            'base_url' => env('LI_BASE_URL', 'http://localhost:3000'),
        ]);

        if (env('USE_SNAPSHOTS', true)) {
            $tokenService->fake();
        }

        $user = $this->service->withToken($tokenService->token())->user();

        $this->assertInstanceOf(User::class, $user);
    }

    public function testActAsAnotherUser()
    {
        $this->service->actAs(24);
        $subject = $this->service->user();

        $this->assertInstanceOf(User::class, $subject);
    }
}
