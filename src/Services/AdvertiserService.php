<?php

namespace LiveIntent\Services;

use LiveIntent\Advertiser;

/**
 * @method \LiveIntent\Advertiser find($id)
 * @method \LiveIntent\Advertiser create($attributes)
 * @method \LiveIntent\Advertiser update($attributes)
 */
class AdvertiserService extends AbstractResourceService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/advertiser';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = Advertiser::class;
}
