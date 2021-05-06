<?php

namespace LiveIntent\Services;

use LiveIntent\User;

/**
 * @method \LiveIntent\User user()
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
}
