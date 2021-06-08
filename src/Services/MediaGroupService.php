<?php

namespace LiveIntent\Services;

use LiveIntent\MediaGroup;

/**
 * @method \LiveIntent\MediaGroup find($id)
 * @method \LiveIntent\MediaGroup create($attributes)
 * @method \LiveIntent\MediaGroup update($attributes)
 */
class MediaGroupService extends AbstractResourceService
{
    /**
     * The base url for this entity.
     */
    protected $baseUrl = '/media-group';

    /**
     * The resource class for this entity.
     */
    protected $objectClass = MediaGroup::class;
}
