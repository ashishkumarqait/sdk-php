<?php

namespace LiveIntent\Services;

use LiveIntent\Campaign;

/**
 * @method \LiveIntent\Campaign find($id)
 * @method \LiveIntent\Campaign create($attributes)
 * @method \LiveIntent\Campaign update($attributes)
 */
class CampaignService extends AbstractResourceService
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
