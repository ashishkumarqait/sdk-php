<?php

namespace LiveIntent\Services;

use LiveIntent\Newsletter;

/**
 * @method \LiveIntent\Newsletter find($id)
 * @method \LiveIntent\Newsletter create($attributes)
 * @method \LiveIntent\Newsletter update($attributes)
 */
class NewsletterService extends AbstractResourceService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/newsletter';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = Newsletter::class;
}
