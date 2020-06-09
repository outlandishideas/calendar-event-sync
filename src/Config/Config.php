<?php

namespace Outlandish\CalendarEventSync\Config;


/**
 * Class Config
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class Config
{
    const ACCESS_TOKEN_KEY = 'google_calendar_access_token';
    const CALENDAR_ID_KEY = 'google_calendar_id';


    /**
     * Store the access token in the wp_options table
     *
     * @param array $accessToken The access token to store
     */
    public function setAccessToken(array $accessToken)
    {
        $wasAdded = add_option(static::ACCESS_TOKEN_KEY, json_encode($accessToken));

        if (!$wasAdded) {
            update_option(static::ACCESS_TOKEN_KEY, json_encode($accessToken));
        }
    }

    /**
     * Retreive the access token from the wp_options table
     *
     * @return array|null
     */
    public function getAccessToken()
    {
        return json_decode(get_option(static::ACCESS_TOKEN_KEY), true);
    }

    /**
     * Retreive the calendar id
     *
     * Either return the calendar id if set as a constant or return
     * it from the wp_options table.
     *
     * @return string|null
     */
    public function getCalendarId()
    {
        if (defined('GOOGLE_CALENDAR_ID')) {
            return GOOGLE_CALENDAR_ID;
        }

        return get_option(static::CALENDAR_ID_KEY);
    }

    /**
     * Store the access token in the wp_options table
     *
     * @param string $calendarId The calendar id to store
     */
    public function setCalendarId($calendarId)
    {
        add_option(static::CALENDAR_ID_KEY, $calendarId);
    }

}