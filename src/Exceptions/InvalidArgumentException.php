<?php

namespace LiveIntent\Exceptions;

class InvalidArgumentException extends \Exception
{
    /**
     * Create a new invalid argument exception.
     *
     * @param  mixed  $argument
     * @param  string  $message
     * @return static
     */
    public static function factory($argument, $message)
    {
        $message = trim($message, '.') . '.';

        $argument = json_encode($argument);

        return new static($message . " Argument: `{$argument}`");
    }
}
