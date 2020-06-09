<?php

namespace Outlandish\CalendarEventSync\Tests\Services;

use Outlandish\CalendarEventSync\Exceptions\ExternalEventExistsException;
use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Repositories\ExternalEventRepository;
use Outlandish\CalendarEventSync\Services\ExternalEventStoreService;
use Mockery as m;
use Outlandish\CalendarEventSync\Tests\TestCase;

class ExternalEventStoreServiceTest extends TestCase
{
    /**
     * @var m\LegacyMockInterface|m\MockInterface|ExternalEventRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $externalEventIdKey;

    protected function setUp()
    {
        parent::setUp();
        $this->repository = m::mock(ExternalEventRepository::class);
        $this->externalEventIdKey = 'outlandish_calender_event_sync_event_id';
    }

    /** @test */
    public function it_throws_an_exception_if_the_event_already_exists()
    {
        $id = '1';

        $event = m::mock(ExternalEvent::class);
        $event->expects('getId')->andReturn($id);
        $event->expects('getSummary')->once();

        $this->repository->expects('existsByExternalId')->with($this->externalEventIdKey, $id)->andReturn(true);

        $this->expectException(ExternalEventExistsException::class);

        $this->getService()->storeEvent($event);
    }

    /** @test */
    public function it_stores_an_event_if_it_doesnt_already_exist()
    {
        $event = m::mock(ExternalEvent::class);
        $event->expects('getId')->once();

        $this->repository->expects('existsByExternalId')->andReturn(false);
        $this->repository->expects('add')->with($event)->once();

        $this->getService()->storeEvent($event);
    }

    protected function getService()
    {
        return new ExternalEventStoreService($this->repository, $this->externalEventIdKey);
    }
}
