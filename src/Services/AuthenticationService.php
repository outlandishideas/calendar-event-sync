<?php

namespace Outlandish\CalendarEventSync\Services;


use Google_Client;
use Outlandish\CalendarEventSync\Config\Config;
use Outlandish\CalendarEventSync\Exceptions\ClientException;

/**
 * Class AuthenticationService
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync\Services
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class AuthenticationService
{
    /**
     * @var Google_Client
     */
    private $client;

    /**
     * @var Config
     */
    private $config;


    /**
     * AuthenticationService constructor.
     */
    public function __construct(Google_Client $client, Config $config)
    {
        $this->client = $client;
        $this->config = $config;
    }

    public function createAuthUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function fetchAccessTokenWithAuthCode($code)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($code);

        if (array_key_exists('error', $accessToken)) {
            throw ClientException::couldNotFetchAccessTokenWithAuthCode($accessToken);
        }

        $this->config->setAccessToken($accessToken);
    }

    public function authenticate()
    {
        try {
            $this->client->setAccessToken($this->config->getAccessToken());
        } catch (\InvalidArgumentException $e) {
            throw ClientException::accessTokenIsInvalid();
        }

        if ($this->client->isAccessTokenExpired()) {

            if (!$this->client->getRefreshToken()) {
                throw ClientException::accessTokenCannotBeRenewed();
            }

            $this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());

            $this->config->setAccessToken($this->client->getAccessToken());

        }
    }

}