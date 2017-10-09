<?php
declare(strict_types=1);
namespace NamelessCoder\EventEngine;

interface EventDispatcherInterface
{
    public function addHandler(EventHandlerInterface $eventHandler);
    public function dispatch(EventInterface $event, bool $allowStopPropagation = false): EventInterface;
    public function create(EventTypeInterface $type, EventDataInterface $data = null, EventInterface $initiatingEvent = null): EventInterface;
}
