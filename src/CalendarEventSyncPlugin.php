<?php

namespace Outlandish\CalendarEventSync;


use Google_Service_Calendar;
use Outlandish\CalendarEventSync\Commands\Events;
use Outlandish\CalendarEventSync\Config\Config;
use Outlandish\CalendarEventSync\Factories\GoogleClientFactory;
use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Repositories\ExternalEventRepository;
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
    const EXTERNAL_EVENT_ID_KEY = 'outlandish_calender_event_sync_event_id';

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

    /**
     * Construct the plugin with some environment variables
     *
     * @param string $clientId The google client id
     * @param string $clientSecret The google secret
     * @param string $projectId The project id for the google client
     * @param string $calendarId The google calendar id to fetch events from (email address)
     */
    public function __construct($clientId, $clientSecret, $projectId, $calendarId)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->projectId = $projectId;
        $this->calendarId = $calendarId;
    }

    /**
     * Initialise the plugin
     *
     * @throws \Google_Exception
     */
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

        $storeService = new ExternalEventStoreService($repository, static::EXTERNAL_EVENT_ID_KEY);

        $eventCommand = new Events(
            $authentication,
            $fetchService,
            $storeService
        );
        WP_CLI::add_command('events', $eventCommand);


        add_action(static::STORE_EVENT_ACTION, [CalendarEventSyncPlugin::class, 'defaultStoreStrategy'], 10, 2);
        add_action(static::STORE_EVENT_ACTION, [CalendarEventSyncPlugin::class, 'publishEvent'], 50, 1);
    }

    /**
     * Default storage strategy for the External Events
     *
     * This method stores the external event to wordpress
     * using the wp_insert_post function and then also stores
     * metadata for the external event id so we know that this
     * event has been stored.
     *
     * The external event object is also updated with the Wordpress
     * post id so that we can do some more stuff at later points in
     * the action.
     *
     * @param ExternalEvent $event The event to save
     * @param string        $postType The post type to save the event as
     */
    public static function defaultStoreStrategy(ExternalEvent $event, $postType)
    {
        $id = wp_insert_post([
            'post_title' => $event->getSummary(),
            'post_type' => $postType,
            'post_status' => 'draft',
            'post_name' => $event->getId()
        ]);

        add_post_meta($id, static::EXTERNAL_EVENT_ID_KEY, $event->getId());

        $event->setPostId($id);
    }

    /**
     * Publish the event that was previously saved during this action
     *
     * This updates the wordpress post representing the external event
     * to set its status to published. It does this later in the action
     * than when we simply store it to allow a user to change some other
     * stuff about it before it is published.
     *
     * @param ExternalEvent $event The event to publish
     */
    public static function publishEvent(ExternalEvent $event)
    {
        if ($event->savedToWordPress()) {
            wp_update_post(['ID' => $event->getPostId(), 'post_status' => 'publish']);
        }
    }
}