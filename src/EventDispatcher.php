<?php
namespace NamelessCoder\EventEngine;

/**
 * Generic EventDispatcher class
 *
 * Simplest possible implementation of EventDispatcherInterface.
 * Keeps track of registered EventHandlers and dispatches Events
 * to them accordingly.
 */
class EventDispatcher implements EventDispatcherInterface
{
    /**
     * Array in which we keep track of registered EventHandlers
     * by indexing them according to which events they handle.
     *
     * @var EventHandlerInterface[][]
     */
    protected $handlers = [];

    public function addHandler(EventHandlerInterface $eventHandler)
    {
        foreach ($eventHandler->getHandledEventTypes() as $handledEventType) {
            if (!isset($this->handlers[$handledEventType])) {
                $this->handlers[$handledEventType] = [$eventHandler];
            } elseif (!in_array($eventHandler, $this->handlers[$handledEventType])) {
                $this->handlers[$handledEventType][] = $eventHandler;
            }
        }
    }

    public function dispatch(EventInterface $event, bool $allowStopPropagation = false): EventInterface
    {
        foreach ($this->handlers[$event->getType()->getName()] ?? [] as $eventHandler) {
            $event = $eventHandler->handleEvent($event);
            if ($allowStopPropagation && $event->isStopped()) {
                return $event;
            }
        }
        return $event;
    }

    public function create(EventTypeInterface $type, EventDataInterface $data = null, EventInterface $initiatingEvent = null): EventInterface
    {
        return new Event($type, $data, $initiatingEvent);
    }
}
