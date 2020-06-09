<?php

namespace Outlandish\CalendarEventSync\Tests\Config;

use Outlandish\CalendarEventSync\Config\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /** @test */
    public function it_stores_an_access_token_in_the_options_table()
    {
        $accessToken = ['access_token' => '123814279387'];

        $this->getConfig()->setAccessToken($accessToken);

        $this->assertAccessTokenStored($accessToken);
    }

    /** @test */
    public function it_overwrites_an_existing_access_token_if_one_already_exists()
    {
        $oldAccessToken = ['access_token' => '123814279387'];
        add_option('google_calendar_access_token', json_encode($oldAccessToken));

        $newAccessToken = ['access_token' => '0987654321'];

        $this->getConfig()->setAccessToken($newAccessToken);

        $this->assertAccessTokenStored($newAccessToken);
    }

    /** @test */
    public function it_retrieves_an_access_token_in_the_options_table()
    {
        $config = new Config();
        $accessToken = ['access_token' => '123814279387'];

        add_option('google_calendar_access_token', json_encode($accessToken));

        $result = $this->getConfig()->getAccessToken();

        $this->assertEquals($result, $accessToken);
    }

    /** @test */
    public function it_returns_null_if_no_access_token_is_stored()
    {
        $result = $this->getConfig()->getAccessToken();

        $this->assertNull($result);
    }

    protected function getConfig()
    {
        return new Config();
    }

    /**
     * Assert that the access token was stored in the wp_options table
     *
     * @param $accessToken
     */
    protected function assertAccessTokenStored($accessToken)
    {
        $stored = json_decode(get_option('google_calendar_access_token'), true);
        $this->assertEquals($stored, $accessToken);
    }

    protected function tearDown()
    {
        delete_option('google_calendar_access_token');
        parent::tearDown();
    }


}
