<?php

namespace LiveIntent\Services;

use LiveIntent\User;

/**
 * @method \LiveIntent\User user()
 * @method \Illuminate\Http\Client\Response actAs(int $id)
 */
class AuthService extends AbstractService
{
    /**
     * Get the currently authenticated user.
     *
     * @return \LiveIntent\User
     */
    public function user()
    {
        $response = $this->request('get', 'me');

        return new User($response->json());
    }

    /**
     * Log in as another user.
     *
     * @param int $id
     * @return \LiveIntent\User
     */
    public function actAs(int $id)
    {
        return $this->tokenService->actAs($id);
    }
}
