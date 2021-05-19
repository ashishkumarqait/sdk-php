<?php

namespace LiveIntent\Services;

use LiveIntent\User;

/**
 * @method \LiveIntent\User user()
 * @method \Illuminate\Http\Client\Response actAs(int $userId)
 */
class AuthService extends BaseService
{
    /**
     * Get the currently authenticated user.
     *
     * @return \LiveIntent\User
     */
    public function user()
    {
        return new User(
            $this->request('get', 'me')->json()
        );
    }

    /**
     * Log in as another user.
     *
     * @param int $userId
     * @return \LiveIntent\User
     */
    public function actAs(int $userId)
    {
        return $this->tokenService->actAs($userId);
    }
}
