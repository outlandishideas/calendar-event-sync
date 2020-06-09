<?php

namespace Outlandish\CalendarEventSync\Tests\Repositories;

use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Repositories\ExternalEventRepository;
use Outlandish\CalendarEventSync\Tests\TestCase;
use Mockery as m;
use Brain\Monkey\Actions;
use Brain\Monkey\Functions;

class ExternalEventRepositoryTest extends TestCase
{
    /** @test */
    public function it_is_a_fake_test()
    {
        $this->assertTrue(true);
    }

    public function it_adds_an_external_event()
    {
        $repository = new ExternalEventRepository();
        $event = m::mock(ExternalEvent::class);

        Actions\expectDone('outlandish/calendar-sync/adding-event')
            ->once()
            ->with($event);

        $repository->add($event);
    }

    public function it_returns_false_if_no_event_exists_with_the_external_id()
    {
        $repository = new ExternalEventRepository();
        $externalId = '123456';

        $result = $repository->existsByExternalId($externalId);

        $this->assertFalse($result);
    }

    public function it_returns_true_if_an_event_exists_with_the_external_id()
    {
        $repository = new ExternalEventRepository();
        $externalId = '123456';

        $result = $repository->existsByExternalId($externalId);

        $this->assertTrue($result);
    }

}
