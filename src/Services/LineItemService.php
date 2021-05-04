<?php

namespace LiveIntent\Services;

use LiveIntent\LineItem;

/**
 * @method \LiveIntent\LineItem find($id, $opts = [])
 * @method \LiveIntent\LineItem create($attributes, $opts = [])
 * @method \LiveIntent\LineItem update($attributes, $opts = [])
 */
class LineItemService extends AbstractService
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
