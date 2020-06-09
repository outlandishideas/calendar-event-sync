<?php

namespace Outlandish\CalendarEventSync\Tests\Factories;

use Google_Client;
use Outlandish\CalendarEventSync\Factories\GoogleClientFactory;
use Outlandish\CalendarEventSync\Tests\TestCase;

class GoogleClientFactoryTest extends TestCase
{
    /** @test */
    public function it_creates_a_google_client_object()
    {
        $client = $this->createFactory()->create();

        $this->assertInstanceOf(Google_Client::class, $client);
    }

    /** @test */
    public function it_creates_the_same_google_client_object()
    {
        $factory = $this->createFactory();
        $client1 = $factory->create();
        $client2 = $factory->create();

        $this->assertSame($client1, $client2);
    }

    /** @test */
    public function it_creates_the_google_client_with_the_correct_client_id()
    {
        $client = $this->createFactory()->create();

        $this->assertEquals('1234567', $client->getClientId());
    }

    /** @test */
    public function it_creates_the_google_client_with_the_correct_client_secret()
    {
        $client = $this->createFactory()->create();

        $this->assertEquals('secret', $client->getClientSecret());
    }

    protected function createFactory()
    {
        $clientId = '1234567';
        $clientSecret = 'secret';
        $projectId = '1';

        return new GoogleClientFactory($clientId, $clientSecret, $projectId);
    }
}
