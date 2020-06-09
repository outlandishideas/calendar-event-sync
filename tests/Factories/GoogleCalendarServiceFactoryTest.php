<?php

namespace Outlandish\CalendarEventSync\Tests\Factories;

use Google_Client;
use Google_Service_Calendar;
use Outlandish\CalendarEventSync\Factories\GoogleCalendarServiceFactory;
use PHPUnit\Framework\TestCase;
use Mockery as m;

class GoogleCalendarServiceFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_the_service_with_a_client()
    {
        $client = m::mock(Google_Client::class);
        $factory = new GoogleCalendarServiceFactory($client);

        $result = $factory->create();

        $this->assertInstanceOf(Google_Service_Calendar::class, $result);
        $this->assertSame($client, $result->getClient());
    }
}
