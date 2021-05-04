<?php

namespace LiveIntent\Relations;

abstract class AbstractRelation
{
    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    abstract public function getResults();
}
