<?php

namespace Tests\Services;

use Tests\Fixtures;
use LiveIntent\AdSlot;
use LiveIntent\Exceptions\InvalidRequestException;

class AdSlotServiceTest extends ServiceTestCase
{
    protected $serviceKey = 'adSlots';

    public function testIsFindable()
    {
        $adSlot = $this->service->find(Fixtures::adSlotId());
        $this->assertInstanceOf(AdSlot::class, $adSlot);

        $adSlot = $this->service->find(Fixtures::adSlotHash());
        $this->assertInstanceOf(AdSlot::class, $adSlot);
    }

    public function testIsCreatableViaAttributesArray()
    {
        $adSlot = $this->service->create([
            'name' => 'SDK Test',
            'newsletter' => Fixtures::newsletterHash(),
            'type' => 'image',
            'mediaType' => 'newsletter',
            'sizes' => ''
        ]);

        $this->assertNotNull($adSlot->id);
        $this->assertInstanceOf(AdSlot::class, $adSlot);
    }
}
