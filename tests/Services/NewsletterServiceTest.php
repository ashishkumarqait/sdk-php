<?php

namespace Tests\Services;

use Tests\Fixtures;
use LiveIntent\Newsletter;
use LiveIntent\Exceptions\InvalidRequestException;

class NewsletterServiceTest extends ServiceTestCase
{
    protected $serviceKey = 'newsletters';

    public function testIsFindable()
    {
        $newsletter = $this->service->find(Fixtures::newsletterId());
        $this->assertInstanceOf(Newsletter::class, $newsletter);

        $newsletter = $this->service->find(Fixtures::newsletterHash());
        $this->assertInstanceOf(Newsletter::class, $newsletter);
    }

    public function testIsCreatableViaAttributesArray()
    {
        $newsletter = $this->service->create([
            'name' => 'SDK Test',
            'publisher' => Fixtures::publisherHash(),
            'category' => 1
        ]);

        $this->assertNotNull($newsletter->id);
        $this->assertInstanceOf(Newsletter::class, $newsletter);
    }

    public function testIsCreatableViaResourceInstance()
    {
        $newsletter = new Newsletter([
            'name' => 'SDK Test',
            'publisher' => Fixtures::publisherHash(),
            'category' => 1
        ]);

        $newsletter = $this->service->create($newsletter);

        $this->assertNotNull($newsletter->id);
        $this->assertInstanceOf(Newsletter::class, $newsletter);
    }

    public function testIsUpdateableViaAttributesArray()
    {
        $newsletter = $this->service->find(Fixtures::newsletterId());

        $updatedName = 'SDK_TEST_UPDATE_NAME';

        $newsletter = $this->service->update([
            'id' => $newsletter->id,
            'version' => $newsletter->version,
            'name' => $updatedName,
        ]);

        $this->assertEquals($updatedName, $newsletter->name);
        $this->assertInstanceOf(Newsletter::class, $newsletter);
    }

    public function testIsUpdateableViaResourceInstance()
    {
        $newsletter = $this->service->find(Fixtures::newsletterId());
        $updatedName = 'SDK_TEST_UPDATE_NAME';

        $newsletter->name = $updatedName;
        $lineItem = $this->service->update($newsletter);

        $this->assertEquals($updatedName, $newsletter->name);
        $this->assertInstanceOf(Newsletter::class, $newsletter);
    }

    public function testCanCreateOrUpdate()
    {
        $originalNewsletter = $this->service->createOrUpdate([
            'name' => 'SDK Test',
            'publisher' => Fixtures::publisherHash(),
            'category' => 1
        ]);

        $this->assertInstanceOf(Newsletter::class, $originalNewsletter);

        $updatedNewsletter = $this->service->createOrUpdate($originalNewsletter);
        $this->assertInstanceOf(Newsletter::class, $updatedNewsletter);
        $this->assertEquals($updatedNewsletter->id, $originalNewsletter->id);
        $this->assertEquals($updatedNewsletter->version, $originalNewsletter->version + 1);
    }

    public function testIsDeletable()
    {
        $newsletter = $this->service->createOrUpdate([
            'name' => 'SDK Test',
            'publisher' => Fixtures::publisherHash(),
            'category' => 1
        ]);

        $this->assertInstanceOf(Newsletter::class, $newsletter);

        $this->service->delete($newsletter->id);

        $deletedNewsletter = $this->service->find($newsletter->id);
        $this->assertEquals('deleted', $deletedNewsletter->status);
    }

    public function testThrowsWhenInvalidDataIsPassed()
    {
        $this->expectException(InvalidRequestException::class);

        $this->service->create([
            'name' => 'SDK Test',
            'publisher' => Fixtures::publisherHash()
        ]);
    }
}
