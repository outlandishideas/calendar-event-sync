<?php

namespace Outlandish\CalendarEventSync\Exceptions;


/**
 * Class ClientException
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync\Exceptions
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class ClientException extends CalendarEventSyncException
{
    public static function couldNotFetchAccessTokenWithAuthCode(array $accessToken)
    {
        return new static("Could not fetch access token with auth code. Recieved " . json_encode($accessToken));
    }

    public static function accessTokenIsInvalid()
    {
        return new static("Access Token is invalid or does not exist.");
    }

    public static function accessTokenCannotBeRenewed()
    {
        return new static("Access token cannot be renewed.");
    }
}