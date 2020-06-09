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

    /**
     * @var ExternalEventRepository
     */
    private $repository;

    private $externalIdKey;

    /**
     * ExternalEventStoreService constructor.
     *
     * @param ExternalEventRepository $repository
     * @param                         $externalIdKey
     */
    public function __construct(ExternalEventRepository $repository, $externalIdKey)
    {
        $this->repository = $repository;
        $this->externalIdKey = $externalIdKey;
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
        $exists = $this->repository->existsByExternalId($this->externalIdKey, $event->getId());

        if ($exists) {
            throw new ExternalEventExistsException("Event {$event->getSummary()} already exists");
        }

        $this->repository->add($event);
    }

}