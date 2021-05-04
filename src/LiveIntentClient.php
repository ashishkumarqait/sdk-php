<?php

namespace LiveIntent;

use LiveIntent\Client\BaseClient;

class LiveIntentClient extends BaseClient
{
    /**
     * A mapping of getters to service classes. This allows developers
     * to access individual services directly as getters on the
     * client, rather than instantiating every single service.
     * @var array<string, class-string>
     */
    protected static $classMap = [
        'advertisers' => Services\AdvertiserService::class,
        'campaigns' => Services\CampaignService::class,
        'insertionOrders' => Services\InsertionOrderService::class,
        'lineItems' => Services\LineItemService::class,
    ];

    /**
     * The already instantiated services.
     *
     * @var array
     */
    protected $services = [];

    /**
     * Dynamically resolve a service instance.
     *
     * @param string $name
     * @return null|\LiveIntent\Services\AbstractService
     */
    public function __get($name)
    {
        if (! \array_key_exists($name, static::$classMap)) {
            return null;
        }

        if (! \array_key_exists($name, $this->services)) {
            $this->services[$name] = new static::$classMap[$name]($this);
        }

        return $this->services[$name];
    }
}
