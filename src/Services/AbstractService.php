<?php

namespace LiveIntent\Services;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class AbstractService extends Factory
{
    use ForwardsCalls;
    use Concerns\MocksRequests;
    use Concerns\HandlesApiErrors;
    use Concerns\AuthenticatesRequests;

    /**
     * The default options to use when creating requests.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The currently pending request.
     *
     * @var \Illuminate\Http\Client\PendingRequest
     */
    private $pendingRequest;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(array $options)
    {
        parent::__construct();

        $this->options = $options;
    }

    /**
     * Create a new pending request instance for this factory.
     *
     * @return \Illuminate\Http\Client\PendingRequest
     */
    protected function newPendingRequest()
    {
        $request = new PendingRequest($this);

        return $request
            ->acceptJson()
            ->timeout(data_get($this->options, 'timeout', 10))
            ->baseUrl(data_get($this->options, 'base_url'))
            ->withOptions(data_get($this->options, 'guzzleOptions', []))
            ->retry(data_get($this->options, 'tries', 1), data_get($this->options, 'retryDelay', 10));
    }

    /**
     * Send the request to the given URL.
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return \Illuminate\Http\Client\Response
     */
    public function request(string $method, string $url, array $options = [])
    {
        $request = tap($this->pendingRequest(), function ($request) {
            $this->authenticateRequest($request);
        });

        $response = $request->send($method, $url, $options);

        $this->handleErrors($response);

        return $response;
    }

    /**
     * Attach a json body to the request.
     *
     * @param array $data
     * @return PendingRequest
     */
    public function withJson(array $data)
    {
        return $this->withBody(json_encode($data), 'application/json');
    }

    /**
     * Get the currently pending request.
     *
     * @return PendingRequest
     */
    public function pendingRequest()
    {
        if (! $this->pendingRequest) {
            $this->pendingRequest = $this->newPendingRequest();
        }

        return $this->pendingRequest;
    }

    /**
     * Execute a method against the current pending request instance.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = $this->forwardCallTo($this->pendingRequest(), $method, $parameters);

        if ($result instanceof PendingRequest) {
            return $this;
        }

        return $result;
    }
}
