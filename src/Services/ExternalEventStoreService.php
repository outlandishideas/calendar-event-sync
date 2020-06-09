<?php

namespace Outlandish\CalendarEventSync\Services;


use Outlandish\CalendarEventSync\Exceptions\ExternalEventExistsException;
use Outlandish\CalendarEventSync\Models\ExternalEvent;
use Outlandish\CalendarEventSync\Models\ExternalEventFactory;
use Outlandish\CalendarEventSync\Repositories\ExternalEventRepository;
use Outlandish\Website\PostTypes\Event;

/**
 * Class ExternalEventStoreService
 *
 * @category Class
 * @package  Outlandish\CalendarEventSync\Services
 * @author   Matthew Kendon <matt@outlandish.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://outlandish.com
 */
class ExternalEventStoreService
{
    const EXTERNAL_ID_KEY = 'outlandish_calender_event_sync_event_id';

    /**
     * @var ExternalEventRepository
     */
    private $repository;

    /**
     * ExternalEventStoreService constructor.
     *
     * @param ExternalEventRepository $repository
     */
    public function __construct(ExternalEventRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Stores an event using the ExternalEventRepository
     *
     * @param ExternalEvent $event The event to store
     *
     * @throws ExternalEventExistsException
     */
    public function storeEvent(ExternalEvent $event)
    {
        $exists = $this->repository->existsByExternalId(static::EXTERNAL_ID_KEY, $event->getId());

        if ($exists) {
            throw new ExternalEventExistsException("Event {$event->getSummary()} already exists");
        }

        $this->repository->add($event);
    }

    public static function defaultStoreStrategy(ExternalEvent $event, $postType)
    {
        $id = wp_insert_post([
            'post_title' => $event->getSummary(),
            'post_type' => $postType,
            'post_status' => 'draft',
            'post_name' => $event->getId()
        ]);

        $event->setPostId($id);

        add_post_meta($id, static::EXTERNAL_ID_KEY, $event->getId());

        wp_update_post(['ID' => $id, 'post_status' => 'publish']);
    }
}