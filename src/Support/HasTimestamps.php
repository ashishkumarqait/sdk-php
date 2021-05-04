<?php

namespace LiveIntent\Support;

trait HasTimestamps
{
    /**
     * Indicates if the resource should be timestamped.
     *
     * @var bool
     */
    protected $timestamps = true;

    /**
     * Determine if the resource uses timestamps.
     *
     * @return bool
     */
    public function usesTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * Get the name of the "created at" prop.
     *
     * @return string|null
     */
    public function getCreatedOnProp()
    {
        return static::CREATED_ON;
    }

    /**
     * Get the name of the "updated at" prop.
     *
     * @return string|null
     */
    public function getUpdatedOnProp()
    {
        return static::UPDATED_ON;
    }
}
