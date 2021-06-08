<?php

namespace LiveIntent\Services;

use LiveIntent\Publisher;

/**
 * @method \LiveIntent\Publisher find($id)
 * @method \LiveIntent\Publisher create($attributes)
 * @method \LiveIntent\Publisher update($attributes)
 */
class PublisherService extends AbstractResourceService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/publisher';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = Publisher::class;
}
