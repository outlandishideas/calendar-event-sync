<?php

namespace Outlandish\CalendarEventSync\Tests\Services;

use Google_Exception;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Outlandish\CalendarEventSync\Config\Config;
use Outlandish\CalendarEventSync\Exceptions\CalendarFetchException;
use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Services\CalendarItemFetchService;
use Outlandish\CalendarEventSync\Tests\TestCase;
use Mockery as m;

class CalendarItemFetchServiceTest extends TestCase
{
    /**
     * @var Google_Service_Calendar|m\LegacyMockInterface|m\MockInterface
     */
    protected $calendar;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|Config
     */
    protected $config;

    /**
     * @var \Google_Service_Calendar_Resource_Events|m\LegacyMockInterface|m\MockInterface
     */
    protected $events;

    protected function setUp()
    {
        parent::setUp();
        $this->calendar = m::mock(Google_Service_Calendar::class);
        $this->calendar->events = $this->events = m::mock(\Google_Service_Calendar_Resource_Events::class);
        $this->config = m::mock(Config::class);
    }


    /** @test */
    public function it_returns_an_empty_array_if_no_events_fetched()
    {
        $events = m::mock(\Google_Service_Calendar_Events::class);
        $events->expects('getItems')->andReturn([]);

        $this->config->expects('getCalendarId')->andReturn(1);
        $this->events->expects('listEvents')->andReturn($events);

        $results = $this->getService()->fetch(10);

        $this->assertIsArray($results);
        $this->assertEmpty($results);
    }

    /** @test */
    public function it_creates_one_or_more_external_events_and_returns_them()
    {
        $event = m::mock(Google_Service_Calendar_Event::class);

        $events = m::mock(\Google_Service_Calendar_Events::class);
        $events->expects('getItems')->andReturn([$event]);

        $this->config->expects('getCalendarId')->andReturn(1);
        $this->events->expects('listEvents')->andReturn($events);

        $results = $this->getService()->fetch(10);

        $this->assertContainsOnlyInstancesOf(ExternalEvent::class, $results);
    }

    /** @test */
    public function it_throws_an_exception_if_google_doesnt_work()
    {
        $this->config->expects('getCalendarId')->andReturn(1);
        $this->events->expects('listEvents')->andThrow(Google_Exception::class);

        $this->expectException(CalendarFetchException::class);

        $this->getService()->fetch(10);
    }

    protected function getService()
    {
        return new CalendarItemFetchService($this->calendar, $this->config);
    }
}
