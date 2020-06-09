<?php

namespace Outlandish\CalendarEventSync\Services;


use Google_Exception;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Outlandish\CalendarEventSync\Config\Config;
use Outlandish\CalendarEventSync\Exceptions\CalendarFetchException;
use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Models\ExternalEventFactory;
use Outlandish\CalendarEventSync\Models\GoogleClient;

/**
 * Class GoogleCalendarItemFetchService
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync\Services
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class CalendarItemFetchService
{
    /**
     * @var Google_Service_Calendar
     */
    private $calendar;

    /**
     * @var Config
     */
    private $config;

    /**
     * CalendarItemFetchService constructor.
     *
     * @param Google_Service_Calendar $calendar The calendar service
     * @param Config                  $config The config object
     */
    public function __construct(
        Google_Service_Calendar $calendar,
        Config $config
    )
    {
        $this->calendar = $calendar;
        $this->config = $config;
    }

    /**
     * @param $maxResults
     *
     * @return ExternalEvent[]
     */
    public function fetch($maxResults)
    {
        $this->calendar->getClient()->setAccessToken($this->config->getAccessToken());

        $optParams = array(
            'maxResults' => $maxResults,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c'),
        );

        try {
            $results = $this->calendar->events->listEvents($this->config->getCalendarId(), $optParams);
        } catch (Google_Exception $e) {
            throw new CalendarFetchException($e->getMessage(), $e->getCode(), $e);
        }

        /** @var Google_Service_Calendar_Event[] $events */
        $events = $results->getItems();

        return array_map(function (Google_Service_Calendar_Event $event) {
            return new ExternalEvent($event);
        }, $events);
    }

}