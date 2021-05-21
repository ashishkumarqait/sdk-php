<?php

namespace LiveIntent\Client;

use GuzzleHttp\Cookie\CookieJar;
use LiveIntent\Services\ServiceFactory;

abstract class AbstractClient
{
    /**
     * The global client options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The service factory to ease instantiating new services.
     *
     * @var \LiveIntent\Services\ServiceFactory
     */
    private $serviceFactory;

    /**
     * Create a new client instance.
     *
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    /**
     * Impersonate the given user when issuing requests.
     *
     * @param int $userId
     * @return $this
     */
    public function actingAs(int $userId)
    {
        $this->options['act_as_user_id'] = $userId;

        return $this;
    }

    /**
     * Impersonate the given user when issuing requests.
     *
     * @param int $userId
     * @return $this
     */
    public function actAs(int $userId)
    {
        return $this->actingAs($userId);
    }

    /**
     * Specify an authorization token for all requests.
     *
     * @param  string  $token
     * @param  string  $type
     * @return $this
     */
    public function withToken($token, $type = 'Bearer')
    {
        $this->options['headers']['Authorization'] = trim($type.' '.$token);

        return $this;
    }

    /**
     * Set the options that should be used by the client.
     *
     * @return $this
     */
    public function withCookies(array $cookies, string $domain)
    {
        $this->options = array_merge_recursive($this->options, [
            'cookies' => CookieJar::fromArray($cookies, $domain),
        ]);

        return $this;
    }

    /**
     * Instruct the client to use fake responses.
     *
     * @param  callable|array  $callback
     * @return $this
     */
    public function fake()
    {
        $this->options['shouldFake'] = true;

        return $this;
    }

    /**
     * Save request/response pairs for later mocking.
     *
     * @return $this
     */
    public function record()
    {
        $this->options['shouldRecord'] = true;

        return $this;
    }

    /**
     * Expose the raw request service as a method so it can be called
     * like LiveIntentClient::request() in some special contexts.
     *
     * @return null|\LiveIntent\Services\BaseService
     */
    public function request()
    {
        return $this->request;
    }

    /**
     * Dynamically resolve a service instance. This makes it easy
     * to access individual services directly as getters on the
     * client rather than instantiating every single service.
     *
     * @param string $name
     * @return null|\LiveIntent\Services\BaseService
     */
    public function __get($name)
    {
        if (null === $this->serviceFactory) {
            $this->serviceFactory = new ServiceFactory($this->options);
        }

        return $this->serviceFactory->make($name, $this->options);
    }
}
