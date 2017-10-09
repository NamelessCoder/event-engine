<?php
declare(strict_types=1);
namespace NamelessCoder\EventEngine;

interface EventInterface
{
    public function getType(): EventTypeInterface;
    public function getData(): EventDataInterface;
    public function getInitiatingEvent(): EventInterface;
    public function stopPropagation(): bool;
    public function isStopped(): bool;
    public function getCreationTime(): float;
    public function getDuration(): float;
}
