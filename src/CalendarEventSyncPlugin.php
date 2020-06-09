<?php

namespace Outlandish\CalendarEventSync;


use Google_Service_Calendar;
use Outlandish\CalendarEventSync\Config\Config;
use Outlandish\CalendarEventSync\Factories\GoogleClientFactory;
use Outlandish\CalendarEventSync\Repositories\ExternalEventRepository;
use Outlandish\CalendarEventSync\Commands\Events;
use Outlandish\CalendarEventSync\Services\AuthenticationService;
use Outlandish\CalendarEventSync\Services\CalendarItemFetchService;
use Outlandish\CalendarEventSync\Services\ExternalEventStoreService;
use WP_CLI;

/**
 * Class CalendarEventSyncPlugin
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class CalendarEventSyncPlugin
{
    const STORE_EVENT_ACTION = 'outlandish/calendar-sync/adding-event';

    /**
     * The Google account client id
     *
     * @var string
     */
    private $clientId;

    /**
     * The Google account client secret
     *
     * @var string
     */
    private $clientSecret;

    /**
     * The Google account project id
     *
     * @var string
     */
    private $projectId;

    /**
     * The Google calendar id
     *
     * @var string
     */
    private $calendarId;

    public function __construct($clientId, $clientSecret, $projectId, $calendarId)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->projectId = $projectId;
        $this->calendarId = $calendarId;
    }

    public function init()
    {
        $repository = new ExternalEventRepository(static::STORE_EVENT_ACTION);

        $config = new Config();
        $clientFactory = new GoogleClientFactory(
            $this->clientId,
            $this->clientSecret,
            $this->projectId
        );
        $client = $clientFactory->create();

        $authentication = new AuthenticationService(
            $client,
            $config
        );

        $fetchService = new CalendarItemFetchService(
            new Google_Service_Calendar($client),
            $config
        );

        $storeService = new ExternalEventStoreService($repository);

        $eventCommand = new Events(
            $authentication,
            $fetchService,
            $storeService
        );
        WP_CLI::add_command( 'events', $eventCommand );


        add_action($storeActionName, [ExternalEventStoreService::class, 'defaultStoreStrategy'], 10, 2);
    }
}