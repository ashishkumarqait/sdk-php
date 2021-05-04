<?php

namespace LiveIntent\Services;

use LiveIntent\Advertiser;

/**
 * @method \LiveIntent\Advertiser find($id, $opts = [])
 * @method \LiveIntent\Advertiser create($attributes, $opts = [])
 * @method \LiveIntent\Advertiser update($attributes, $opts = [])
 */
class AdvertiserService extends AbstractService
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
