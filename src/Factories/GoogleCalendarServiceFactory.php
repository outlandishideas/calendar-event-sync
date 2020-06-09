<?php

namespace Outlandish\CalendarEventSync\Factories;


use Google_Client;
use Google_Service_Calendar;

/**
 * Class GoogleCalendarServiceFactory
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync\Factories
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class GoogleCalendarServiceFactory
{
    /**
     * @var GoogleClientFactory
     */
    private $client;

    public function __construct(Google_Client $client)
    {
        $this->client = $client;
    }

    public function create()
    {
        return new Google_Service_Calendar($this->client);
    }
}