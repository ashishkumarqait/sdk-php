<?php

namespace LiveIntent\Services;

use LiveIntent\InsertionOrder;

/**
 * @method \LiveIntent\InsertionOrder find($id, $opts = [])
 * @method \LiveIntent\InsertionOrder create($attributes, $opts = [])
 * @method \LiveIntent\InsertionOrder update($attributes, $opts = [])
 */
class InsertionOrderService extends AbstractService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/insertion-order';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = InsertionOrder::class;
}
