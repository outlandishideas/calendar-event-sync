<?php

namespace Outlandish\CalendarEventSync\Tests\Services;

use Google_Client;
use InvalidArgumentException;
use Mockery as m;
use Outlandish\CalendarEventSync\Config\Config;
use Outlandish\CalendarEventSync\Exceptions\ClientException;
use Outlandish\CalendarEventSync\Services\AuthenticationService;
use Outlandish\CalendarEventSync\Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    /**
     * @var Google_Client|m\LegacyMockInterface|m\MockInterface
     */
    protected $client;

    /**
     * @var m\LegacyMockInterface|m\MockInterface|\Outlandish\CalendarEventSync\Config\Config
     */
    protected $config;

    protected function setUp()
    {
        parent::setUp();
        $this->client = m::mock(Google_Client::class);
        $this->config = m::mock(Config::class);
    }

    /** @test */
    public function it_returns_the_generated_auth_url()
    {
        $authUrl = 'https://google.com';
        $this->client->shouldReceive('createAuthUrl')->andReturn($authUrl);

        $result = $this->getService()->createAuthUrl();

        $this->assertEquals($result, $authUrl);
    }

    /** @test */
    public function it_gets_an_access_token_using_the_auth_code_and_stores_it()
    {
        $authCode = '123456';
        $accessToken = [
            'token' => '1223522382598'
        ];

        $this->client->shouldReceive('fetchAccessTokenWithAuthCode')
            ->with($authCode)
            ->andReturn($accessToken);

        $this->config->expects('setAccessToken')->with($accessToken)->once();

        $this->getService()->fetchAccessTokenWithAuthCode($authCode);
    }


    /** @test */
    public function it_throws_an_exception_if_it_recieves_an_error_instead_of_an_access_token()
    {
        $authCode = '123456';
        $accessToken = [
            'error' => '1223522382598'
        ];

        $this->client->shouldReceive('fetchAccessTokenWithAuthCode')
            ->with($authCode)
            ->andReturn($accessToken);

        $this->expectException(ClientException::class);

        $this->getService()->fetchAccessTokenWithAuthCode($authCode);
    }

    /** @test */
    public function it_can_authorize_using_an_access_token()
    {
        $accessToken = ['access_token' => '12345678'];
        $this->config->expects('getAccessToken')->once()->andReturn($accessToken);
        $this->client->expects('setAccessToken')->with($accessToken);
        $this->client->expects('isAccessTokenExpired')->once()->andReturn(false);

        $this->getService()->authenticate();
    }

    /** @test */
    public function it_throws_an_exception_if_the_access_token_doesnt_exist()
    {
        $this->config->expects('getAccessToken')->once()->andReturn(null);
        $this->client->expects('setAccessToken')->with(null)->andThrow(InvalidArgumentException::class);

        $this->expectException(ClientException::class);

        $this->getService()->authenticate();
    }


    /** @test */
    public function it_throws_an_exception_if_the_access_token_is_malformed()
    {
        $malformedAccessToken = [
            'no_access_token' => 'here'
        ];
        $this->config->expects('getAccessToken')->once()->andReturn($malformedAccessToken);
        $this->client->expects('setAccessToken')->with($malformedAccessToken)->andThrow(InvalidArgumentException::class);

        $this->expectException(ClientException::class);

        $this->getService()->authenticate();
    }

    /** @test */
    public function it_can_refresh_an_access_token_if_it_is_expired()
    {
        $oldAccessToken = ['access_token' => '12345678'];
        $newAccessToken = ['access_token' => '123456789'];
        $refreshToken = ['refresh_token' => 'kljdflkdfjlkdfsj'];

        $this->config->expects('getAccessToken')->once()->andReturn($oldAccessToken);
        $this->config->expects('setAccessToken')->once()->with($newAccessToken);

        $this->client->expects('setAccessToken')->with($oldAccessToken);
        $this->client->expects('isAccessTokenExpired')->once()->andReturn(true);
        $this->client->expects('getRefreshToken')->twice()->andReturn($refreshToken);
        $this->client->expects('fetchAccessTokenWithRefreshToken')->with($refreshToken)->once();
        $this->client->expects('getAccessToken')->once()->andReturn($newAccessToken);

        $this->getService()->authenticate();
    }

    /** @test */
    public function it_throws_an_error_if_it_cannot_refresh_an_expired_access_token()
    {
        $oldAccessToken = ['access_token' => '12345678'];

        $this->config->expects('getAccessToken')->once()->andReturn($oldAccessToken);

        $this->client->expects('setAccessToken')->with($oldAccessToken);
        $this->client->expects('isAccessTokenExpired')->once()->andReturn(true);
        $this->client->expects('getRefreshToken')->once()->andReturn(null);

        $this->expectException(ClientException::class);

        $this->getService()->authenticate();
    }

    protected function getService()
    {
        return new AuthenticationService($this->client, $this->config);
    }
}
