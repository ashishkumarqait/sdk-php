<?php

namespace LiveIntent\Client;

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
     * Dynamically resolve a service instance. This makes it easy
     * to access individual services directly as getters on the
     * client rather than instantiating every single service.
     *
     * @param string $name
     * @return null|\LiveIntent\Services\AbstractResourceService
     */
    public function __get($name)
    {
        if (null === $this->serviceFactory) {
            $this->serviceFactory = new ServiceFactory($this->options);
        }

        return $this->serviceFactory->make($name);
    }
}
