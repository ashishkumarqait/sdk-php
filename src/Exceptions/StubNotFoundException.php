<?php

namespace LiveIntent\Exceptions;

use Illuminate\Http\Client\Request;

class StubNotFoundException extends \Exception
{
    /**
     * Create a new exception instance.
     *
     * @return \Exception
     */
    public static function factory(Request $request)
    {
        $context = [
            'method' => $request->method(),
            'url' => $request->url(),
            'body' => $request->body(),
        ];

        return new static("No matching stub found for this request. Context: " . json_encode($context));
    }
}
