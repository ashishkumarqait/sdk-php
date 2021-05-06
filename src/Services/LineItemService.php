<?php

namespace LiveIntent\Services;

use LiveIntent\LineItem;

/**
 * @method \LiveIntent\LineItem find($id)
 * @method \LiveIntent\LineItem create($attributes)
 * @method \LiveIntent\LineItem update($attributes)
 */
class LineItemService extends AbstractResourceService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/strategy';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = LineItem::class;
}
