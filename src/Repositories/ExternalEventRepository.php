<?php

namespace Outlandish\CalendarEventSync\Repositories;


use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Models\ExternalEventFactory;
use Outlandish\CalendarEventSync\PostTypes\Event;
use WP_CLI;

/**
 * Class ExternalEventRepository
 * @package  Outlandish\Website\Repositories
 * @author   Matthew Kendon <matt@outlandish.com>
 * @link     https://outlandish.com
 */
class ExternalEventRepository
{
    /**
     * @var string
     */
    private $postType;

    /**
     * @var string
     */
    private $storeActionName;

    /**
     * ExternalEventRepository constructor.
     *
     * @param string $postType The post type to save external events as
     */
    public function __construct($storeActionName, $postType = 'event')
    {
        $this->postType = $postType;
        $this->storeActionName = $storeActionName;
    }

    public function add(ExternalEvent $event)
    {
        do_action($this->storeActionName, $event, $this->postType);
    }

    /**
     * Check whether an external event has already been added as a post
     *
     * This uses the get_post function to get all posts that have the
     * outlandish_calendar-sync_external_id meta_field that matches the
     * $externalId value passed into the method.
     *
     * If any results are returned, this will return true, otherwise it
     * will return false.
     *
     * @param string $externalId The external id of the event to find
     */
    public function existsByExternalId($key, $externalId)
    {

        $posts = get_posts([
            'post_type' => $this->postType,
            'meta_key' => $key,
            'meta_value' => $externalId
        ]);

        return !empty($posts);

    }
}
