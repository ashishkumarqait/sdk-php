<?php

namespace LiveIntent\Services;

class ServiceFactory
{
    /**
     * The default options to use when creating services.
     *
     * @var array
     */
    protected $options;

    /**
     * The shared token service to use for new services.
     *
     * @var \LiveIntent\Services\TokenService
     */
    protected $tokenService;

    /**
     * A mapping of getters to service classes. This allows developers
     * to access individual services directly as getters on the
     * client, rather than instantiating every single service.
     * @var array<string, class-string>
     */
    protected static $classMap = [
        'advertisers' => AdvertiserService::class,
        'auth' => AuthService::class,
        'campaigns' => CampaignService::class,
        'insertionOrders' => InsertionOrderService::class,
        'lineItems' => LineItemService::class,
    ];

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->options = $options;

        $this->tokenService = new TokenService($options);
    }

    /**
     * Dynamically resolve a service instance.
     *
     * @param string $name
     * @return null|\LiveIntent\Services\AbstractService
     */
    public function make($name)
    {
        if (! \array_key_exists($name, static::$classMap)) {
            return null;
        }

        $service = static::$classMap[$name];

        return tap(new $service($this->options), function ($service) {
            $service->setTokenService($this->tokenService);

            if (data_get($this->options, 'shouldRecord')) {
                $service->saveSnapshots();
            }

            if (data_get($this->options, 'shouldFake')) {
                $service->fake();
            }
        });
    }
}
