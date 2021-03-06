<?php


namespace Outlandish\CalendarEventSync\Models;

use DateTimeImmutable;
use Google_Service_Calendar_Event;

/**
 * A data transfer object for an external event
 *
 * @category DTO
 * @package  Outlandish\CalendarEventSync\Models
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class ExternalEvent
{
    /**
     * @var Google_Service_Calendar_Event
     */
    private $event;

    /**
     * The Post ID of this external event
     *
     * @var string|null
     */
    private $postId;

    /**
     * ExternalEvent constructor.
     *
     * @param Google_Service_Calendar_Event $event The external event
     */
    public function __construct(Google_Service_Calendar_Event $event)
    {
        $this->event = $event;
    }

    /**
     * Get the id of the external event
     *
     * @return string
     */
    public function getId()
    {
        return $this->event->getId();
    }

    /**
     * Get the url of the external event
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->event->getHtmlLink();
    }

    /**
     * Get the summary of the event
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->event->getSummary();
    }

    /**
     * Get the description of the event
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->event->getDescription();
    }

    /**
     * Get the start time of the event
     *
     * @return DateTimeImmutable
     */
    public function getStartTime()
    {
        return new DateTimeImmutable($this->event->getStart()->getDateTime());
    }


    /**
     * Get the end time of the event
     *
     * @return DateTimeImmutable
     */
    public function getEndTime()
    {
        return new DateTimeImmutable($this->event->getEnd()->getDateTime());
    }

    /**
     * Return whether this post has been saved to WordPress
     *
     * @return bool
     */
    public function savedToWordPress()
    {
        return (bool) $this->postId;
    }

    /**
     * Set the post id of this external event
     *
     * @param string $postId
     *
     * @return void
     */
    public function setPostId($postId)
    {
        $this->postId = $postId;
    }


    /**
     * Get the post id of this external event
     *
     * @return string
     */
    public function getPostId()
    {
        return $this->postId;
    }

}
