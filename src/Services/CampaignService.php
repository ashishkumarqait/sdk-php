<?php

namespace LiveIntent\Services;

use LiveIntent\Campaign;

/**
 * @method \LiveIntent\Campaign find($id, $opts = [])
 * @method \LiveIntent\Campaign create($attributes, $opts = [])
 * @method \LiveIntent\Campaign update($attributes, $opts = [])
 */
class CampaignService extends AbstractService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/campaign';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = Campaign::class;
}
