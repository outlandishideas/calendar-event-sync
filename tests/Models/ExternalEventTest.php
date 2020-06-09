<?php

namespace Outlandish\CalendarEventSync\Tests\Models;

use DateTime;
use DateTimeImmutable;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Tests\TestCase;
use Mockery as m;

class ExternalEventTest extends TestCase
{
    /** @test */
    public function it_is_created_with_a_google_event()
    {
        $googleEvent = new Google_Service_Calendar_Event();
        $event = new ExternalEvent($googleEvent);

        $this->assertInstanceOf(ExternalEvent::class, $event);
    }

    /** @test */
    public function it_gets_the_event_id_from_the_google_event()
    {
        $id = '1';

        $googleEvent = new Google_Service_Calendar_Event();
        $googleEvent->setId($id);

        $event = new ExternalEvent($googleEvent);

        $this->assertEquals($id, $event->getId());
    }

    /** @test */
    public function it_gets_the_event_url_from_the_google_event()
    {
        $url = 'https://google.com';

        $googleEvent = new Google_Service_Calendar_Event();
        $googleEvent->setHtmlLink($url);

        $event = new ExternalEvent($googleEvent);

        $this->assertEquals($url, $event->getUrl());
    }

    /** @test */
    public function it_gets_the_event_summary_from_the_google_event()
    {
        $summary = 'Example Event';

        $googleEvent = new Google_Service_Calendar_Event();
        $googleEvent->setSummary($summary);

        $event = new ExternalEvent($googleEvent);

        $this->assertEquals($summary, $event->getSummary());
    }

    /** @test */
    public function it_gets_the_event_description_from_the_google_event()
    {
        $description = 'Example Event';

        $googleEvent = new Google_Service_Calendar_Event();
        $googleEvent->setDescription($description);

        $event = new ExternalEvent($googleEvent);

        $this->assertEquals($description, $event->getDescription());
    }

    /** @test */
    public function it_gets_the_event_start_time_from_the_google_event()
    {
        $datetime = date('c');

        $start = new Google_Service_Calendar_EventDateTime();
        $start->setDateTime($datetime);

        $googleEvent = new Google_Service_Calendar_Event();
        $googleEvent->setStart($start);

        $event = new ExternalEvent($googleEvent);

        $this->assertInstanceOf(DateTimeImmutable::class, $event->getStartTime());
        $this->assertEquals((new DateTime($datetime))->getTimestamp(), $event->getStartTime()->getTimestamp());
    }


    /** @test */
    public function it_gets_the_event_end_time_from_the_google_event()
    {
        $datetime = date('c');

        $end = new Google_Service_Calendar_EventDateTime();
        $end->setDateTime($datetime);

        $googleEvent = new Google_Service_Calendar_Event();
        $googleEvent->setEnd($end);

        $event = new ExternalEvent($googleEvent);

        $this->assertInstanceOf(DateTimeImmutable::class, $event->getEndTime());
        $this->assertEquals((new DateTime($datetime))->getTimestamp(), $event->getEndTime()->getTimestamp());
    }

    /** @test */
    public function it_confirms_that_it_has_not_been_saved_to_wordpress()
    {
        $googleEvent = new Google_Service_Calendar_Event();

        $event = new ExternalEvent($googleEvent);

        $this->assertFalse($event->savedToWordPress());
    }

    /** @test */
    public function it_can_be_shown_to_have_been_saved_to_the_database()
    {
        $googleEvent = new Google_Service_Calendar_Event();

        $event = new ExternalEvent($googleEvent);

        $wpId = '1';
        //after being saved to the datbase
        $event->setPostId($wpId);

        $this->assertTrue($event->savedToWordPress());
        $this->assertSame($wpId, $event->getPostId($wpId));
    }

}
