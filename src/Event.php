<?php
namespace NamelessCoder\EventEngine;

/**
 * Generic Event class
 *
 * Simplest possible implementation of the EventInterface.
 * Is capable of having any EventType and carrying any
 * EventData, and reference any Event as initiating event.
 *
 * Is tracked with creation time and calculates duration
 * based on whether or not an initiating Event was passed.
 * If there is no initiating event the duration is zero.
 */
class Event implements EventInterface
{
    /**
     * @var EventTypeInterface
     */
    protected $type;

    /**
     * @var EventDataInterface
     */
    protected $data;

    /**
     * @var bool
     */
    protected $stopped = false;

    /**
     * @var EventInterface|null
     */
    protected $initiatingEvent;

    /**
     * @var float
     */
    protected $creationTime;

    public function __construct(EventTypeInterface $type, EventDataInterface $data = null, EventInterface $initiatingEvent = null)
    {
        $this->type = $type;
        $this->data = $data ?? new EventData([]);
        $this->initiatingEvent = $initiatingEvent;
        $this->creationTime = microtime(true);
    }

    public function getType(): EventTypeInterface
    {
        return $this->type;
    }

    public function getData(): EventDataInterface
    {
        return $this->data;
    }

    public function getInitiatingEvent(): EventInterface
    {
        return $this->initiatingEvent ?? $this;
    }

    public function stopPropagation(): bool
    {
        return $this->stopped = true;
    }

    public function isStopped(): bool
    {
        return $this->stopped;
    }

    public function getCreationTime(): float
    {
        return $this->creationTime;
    }

    public function getDuration(): float
    {
        return $this->getCreationTime() - $this->getInitiatingEvent()->getCreationTime();
    }
}
