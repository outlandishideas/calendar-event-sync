<?php

namespace Outlandish\CalendarEventSync\Factories;


use Google_Client;
use Google_Service_Calendar;
use Outlandish\CalendarEventSync\Models\GoogleClient;

/**
 * Class GoogleClientFactory
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync\Factories
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class GoogleClientFactory
{
    /**
     * @var string
     */
    private $clientId;

    /**
     * @var string
     */
    private $clientSecret;

    /**
     * @var string
     */
    private $projectId;

    /**
     * Google_Client Singleton
     *
     * @var null|Google_Client
     */
    private $client;

    public function __construct($clientId, $clientSecret, $projectId)
    {
//        $this->clientId = GOOGLE_CALENDAR_CLIENT_ID;
//        $this->clientSecret = GOOGLE_CALENDAR_CLIENT_SECRET;
//        $this->projectId = GOOGLE_CALENDAR_PROJECT_ID;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->projectId = $projectId;
    }

    /**
     * Creates an instance of the Google_Client and stores it
     *
     * This will create an instance of the Google_Client class,
     * and store that created instance, so if it is asked for it
     * again it will use the stored version.
     *
     * @return Google_Client
     * @throws \Google_Exception
     */
    public function create()
    {
        if (!$this->client) {

            $config = [
                "installed" => [
                    "client_id" => $this->clientId,
                    "project_id" => $this->projectId,
                    "auth_uri" => "https=>//accounts.google.com/o/oauth2/auth",
                    "token_uri" => "https=>//oauth2.googleapis.com/token",
                    "auth_provider_x509_cert_url" => "https=>//www.googleapis.com/oauth2/v1/certs",
                    "client_secret" => $this->clientSecret,
                    "redirect_uris" => [
                        "urn:ietf:wg:oauth:2.0:oob",
                        "http://localhost"
                    ]
                ]
            ];


            $client = new Google_Client();
            $client->setApplicationName('Google Calendar API PHP Quickstart');
            $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
            $client->setAuthConfig($config);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');

            $this->client = $client;
        }


        return $this->client;
    }
}