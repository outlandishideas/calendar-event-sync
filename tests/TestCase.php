<?php

namespace Outlandish\CalendarEventSync\Tests;

use Brain\Monkey;
use Mockery;

/**
 * Base TestCase class for plugin
 *
 * This TestCase sets up the Brain/Monkey package
 * to mock out wordpress functions and to mock
 * out any function.
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync\Tests
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown()
    {

        if ($container = Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
        }

        Monkey\tearDown();
        parent::tearDown();
    }


}
