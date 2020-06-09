<?php

namespace Outlandish\CalendarEventSync\Tests\Commands;

use Outlandish\CalendarEventSync\Commands\Events;
use Outlandish\CalendarEventSync\Exceptions\CalendarFetchException;
use Outlandish\CalendarEventSync\Exceptions\ClientException;
use Outlandish\CalendarEventSync\Exceptions\ExternalEventExistsException;
use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Services\AuthenticationService;
use Mockery as m;
use Outlandish\CalendarEventSync\Services\CalendarItemFetchService;
use Outlandish\CalendarEventSync\Services\ExternalEventStoreService;
use Outlandish\CalendarEventSync\Tests\TestCase;
use WP_CLI\Loggers\Base;

class EventsTest extends TestCase
{
    /**
     * @var m\MockInterface|Base
     */
    protected $logger;

    /**
     * @var m\MockInterface|AuthenticationService
     */
    protected $authentication;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|CalendarItemFetchService
     */
    protected $fetch;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|ExternalEventStoreService
     */
    protected $store;

    protected function setUp()
    {
        parent::setUp();
        $this->authentication = m::mock(AuthenticationService::class);
        $this->fetch = m::mock(CalendarItemFetchService::class);
        $this->store = m::mock(ExternalEventStoreService::class);
        $this->logger = m::spy(Base::class);
        \WP_CLI::set_logger($this->logger);
    }

    /** @test */
    public function it_provides_details_on_how_to_authenticate()
    {
        $authUrl = 'https://google.com';

        $this->authentication
            ->shouldReceive('createAuthUrl')
            ->andReturn($authUrl);

        $this->logger
            ->expects('info')
            ->with($authUrl);

        $this->getEventCommand()->auth([]);
    }

    /** @test */
    public function it_reports_an_error_if_google_client_cannot_be_created()
    {
        $this->authentication
            ->shouldReceive('createAuthUrl')
            ->andThrow(ClientException::class);

        $this->logger
            ->expects('error');

        $this->logger
            ->expects('info')
            ->never();

        $this->getEventCommand()->auth([]);
    }

    /** @test */
    public function it_fetches_an_access_token_if_an_auth_code_is_provided_on_the_command_line()
    {
        $authCode = '12345678';

        $this->authentication
            ->shouldReceive('fetchAccessTokenWithAuthCode')
            ->with($authCode);

        $this->logger
            ->expects('success')
            ->once();

        $this->getEventCommand()->auth([$authCode]);
    }


    /** @test */
    public function it_shows_an_error_if_it_cannot_fetch_access_token()
    {
        $authCode = '12345678';

        $this->authentication
            ->shouldReceive('fetchAccessTokenWithAuthCode')
            ->with($authCode)
            ->andThrow(ClientException::class);

        $this->logger
            ->expects('error')
            ->once();

        $this->logger
            ->expects('success')
            ->never();

        $this->getEventCommand()->auth([$authCode]);
    }

    /** @test */
    public function it_reports_if_it_cannot_authenticate()
    {
        $this->authentication->expects('authenticate')->once()->andThrow(ClientException::class);

        $this->fetch->expects('fetch')->never();

        $this->logger->expects('error')->once();

        $this->getEventCommand()->sync([]);
    }


    /** @test */
    public function it_reports_that_it_cannot_find_any_events()
    {
        $this->authentication->expects('authenticate')->once();

        $this->fetch->expects('fetch')->with(300)->andReturn([]);

        $this->logger->expects('success')->once();

        $this->getEventCommand()->sync([]);
    }



    /** @test */
    public function it_reports_if_there_was_an_error_fetching_events()
    {
        $this->authentication->expects('authenticate')->once();

        $this->fetch->expects('fetch')->with(300)->andThrow(CalendarFetchException::class);

        $this->logger->expects('error')->once();
        $this->logger->expects('success')->never();

        $this->getEventCommand()->sync([]);
    }


    /** @test */
    public function it_stores_the_events_it_fetches()
    {
        $event = m::mock(ExternalEvent::class);

        $this->authentication->expects('authenticate')->once();

        $this->fetch->expects('fetch')->with(300)->andReturn([$event]);

        $this->store->expects('storeEvent')->with($event)->once();

        $this->logger->expects('success')->once();

        $this->getEventCommand()->sync([]);
    }

    /** @test */
    public function it_informs_the_user_if_an_event_already_exists()
    {
        $event = m::mock(ExternalEvent::class);
        $event->expects('getId')->andReturn('1');

        $this->authentication->expects('authenticate')->once();

        $this->fetch->expects('fetch')->with(300)->andReturn([$event]);

        $this->store->expects('storeEvent')
            ->with($event)
            ->once()
            ->andThrow(ExternalEventExistsException::class);

        $this->logger->expects('info')->once();
        $this->logger->expects('success')->once();


        $this->getEventCommand()->sync([]);
    }

    /**
     * Get the class under test
     *
     * @return Events
     */
    protected function getEventCommand()
    {
        return new Events(
            $this->authentication,
            $this->fetch,
            $this->store
        );
    }

    protected function tearDown()
    {
        parent::tearDown();
        unset($this->logger);
        unset($this->authentication);
        unset($this->fetch);
        unset($this->store);
    }

}
