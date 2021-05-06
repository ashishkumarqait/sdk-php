<?php

namespace Tests\Services;

use Tests\Fixtures;
use LiveIntent\LineItem;
use LiveIntent\Exceptions\InvalidRequestException;

class LineItemServiceTest extends ServiceTestCase
{
    protected $serviceKey = 'lineItems';

    public function testIsFindable()
    {
        $lineItem = $this->service->find(Fixtures::lineItemId());
        $this->assertInstanceOf(LineItem::class, $lineItem);

        $lineItem = $this->service->find(Fixtures::lineItemHash());
        $this->assertInstanceOf(LineItem::class, $lineItem);
    }

    public function testIsCreatableViaAttributesArray()
    {
        $lineItem = $this->service->create([
            'name' => 'SDK Test',
            'status' => 'paused',
            'budget' => 0,
            'pacing' => 'even',
            'campaign' => Fixtures::campaignHash(),
        ]);

        $this->assertNotNull($lineItem->id);
        $this->assertInstanceOf(LineItem::class, $lineItem);
    }

    public function testIsCreatableViaResourceInstance()
    {
        $lineItem = new LineItem([
            'name' => 'SDK Test',
            'status' => 'paused',
            'budget' => 0,
            'pacing' => 'even',
            'campaign' => Fixtures::campaignHash(),
        ]);

        $lineItem = $this->service->create($lineItem);

        $this->assertNotNull($lineItem->id);
        $this->assertInstanceOf(LineItem::class, $lineItem);
    }

    public function testIsUpdateableViaAttributesArray()
    {
        $lineItem = $this->service->find(Fixtures::lineItemId());

        $updatedName = 'SDK_TEST_UPDATE_NAME';

        $lineItem = $this->service->update([
            'id' => $lineItem->id,
            'version' => $lineItem->version,
            'name' => $updatedName,
        ]);

        $this->assertEquals($updatedName, $lineItem->name);
        $this->assertInstanceOf(LineItem::class, $lineItem);
    }

    public function testIsUpdateableViaResourceInstance()
    {
        $lineItem = $this->service->find(Fixtures::lineItemId());
        $updatedName = 'SDK_TEST_UPDATE_NAME';

        $lineItem->name = $updatedName;
        $lineItem = $this->service->update($lineItem);

        $this->assertEquals($updatedName, $lineItem->name);
        $this->assertInstanceOf(LineItem::class, $lineItem);
    }

    public function testWhatHappensWhenThereIsAnError()
    {
        // TODO move
        $this->expectException(InvalidRequestException::class);

        $this->service->create([
            'name' => 'SDK Test',
            'budget' => 0,
            'pacing' => 'even',
            'campaign' => Fixtures::campaignHash(),
        ]);
    }
}
