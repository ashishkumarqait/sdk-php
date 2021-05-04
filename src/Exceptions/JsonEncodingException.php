<?php

namespace LiveIntent\Exceptions;

class JsonEncodingException extends \RuntimeException
{
    /**
     * Create a new JSON encoding exception for an attribute.
     *
     * @param  mixed  $resource
     * @param  mixed  $key
     * @param  string  $message
     * @return static
     */
    public static function forAttribute($resource, $key, $message)
    {
        $class = get_class($resource);

        return new static("Unable to encode attribute [{$key}] for resource [{$class}] to JSON: {$message}.");
    }
}
