<?php

namespace LiveIntent\Client;

interface ClientInterface
{
    /**
     * Issue a request to the api.
     *
     * @param string $method
     * @param string $path
     * @param null|array $data
     * @param array $opts
     * @return \Illuminate\Http\Client\Response
     */
    public function request($method, $path, $data = null, $opts = []);
}
