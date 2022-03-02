<?php

namespace LiveIntent\Services;

use LiveIntent\AdSlot;

/**
 * @method \LiveIntent\AdSlot find($id)
 * @method \LiveIntent\AdSlot create($attributes)
 * @method \LiveIntent\AdSlot update($attributes)
 */
class AdSlotService extends AbstractResourceService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/ad-slot';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = AdSlot::class;
}
