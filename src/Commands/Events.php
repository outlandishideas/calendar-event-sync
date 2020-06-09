<?php

namespace Outlandish\CalendarEventSync\Commands;

use Google_Client;
use Google_Service_Calendar;
use InvalidArgumentException;
use Outlandish\CalendarEventSync\Exceptions\CalendarFetchException;
use Outlandish\CalendarEventSync\Exceptions\ClientException;
use Outlandish\CalendarEventSync\Exceptions\ExternalEventExistsException;
use Outlandish\CalendarEventSync\Models\ExternalEventFactory;
use Outlandish\CalendarEventSync\Repositories\ExternalEventRepository;
use Outlandish\CalendarEventSync\Services\AuthenticationService;
use Outlandish\CalendarEventSync\Services\CalendarItemFetchService;
use Outlandish\CalendarEventSync\Services\ExternalEventStoreService;
use WP_CLI;

/**
 * Class Events
 * @package  Outlandish\Website\Commands
 * @author   Matthew Kendon <matt@outlandish.com>
 * @link     https://outlandish.com
 */
class Events
{
    /**
     * The fetch service to get items from the calendar
     *
     * @var CalendarItemFetchService
     */
    protected $fetchService;

    /**
     * Service for storing external events once fetched
     *
     * @var ExternalEventStoreService
     */
    protected $storeService;

    /**
     * Service for authenticating a user
     *
     * @var AuthenticationService
     */
    private $authentication;

    public function __construct(
        AuthenticationService $authentication,
        CalendarItemFetchService $fetchService,
        ExternalEventStoreService $storeService
    )
    {
        $this->authentication = $authentication;
        $this->fetchService = $fetchService;
        $this->storeService = $storeService;
    }

    /**
     * Sync command to sync events from google calendar
     *
     * @param $args
     */
    public function sync($args)
    {
        try {
            $this->authentication->authenticate();
        } catch (ClientException $e) {
            WP_CLI::error(ClientException::class . " {$e->getMessage()}", false);

            return;
        }

        try {
            $events = $this->fetchService->fetch(300);
        } catch (CalendarFetchException $e) {
            WP_CLI::error(CalendarFetchException::class . " {$e->getMessage()}", false);

            return;
        }


        if (empty($events)) {
            WP_CLI::success("No upcoming events found.");

            return;
        }

        $createdCount = 0;
        $count = count($events);

        foreach ($events as $event) {
            try {
                $this->storeService->storeEvent($event);
                $createdCount++;
            } catch (ExternalEventExistsException $e) {
                WP_CLI::log("Fetched event \"{$event->getId()}\" already exists.");
            }
        }

        WP_CLI::success("Fetched {$count} events from calendar. Created {$createdCount}");
    }

    public function auth($args)
    {
        if (empty($args)) {

            try {
                $url = $this->authentication->createAuthUrl();
            } catch (ClientException $e) {
                WP_CLI::error("Google Client could not be created. Have you set up the client correctly", false);
                return;
            }

            WP_CLI::log("Please visit the link below to authorize the account");
            WP_CLI::log($url);

        } else {
            $accessToken = $args[0];

            try {
                $this->authentication->fetchAccessTokenWithAuthCode($accessToken);
            } catch (ClientException $exception) {
                WP_CLI::error("Google Client did not allow the user to be logged in", false);
                return;
            }

            WP_CLI::success("Successfully created access token");
        }
    }

}
