<?php

namespace LiveIntent\Services;

use LiveIntent\User;

/**
 * @method \LiveIntent\LineItem find($id)
 * @method \LiveIntent\LineItem create($attributes)
 * @method \LiveIntent\LineItem update($attributes)
 */
class UserService extends AbstractResourceService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/auth/user';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = User::class;
}
