<?php
declare(strict_types=1);
namespace NamelessCoder\EventEngine;

interface EventHandlerInterface
{
    public function getHandledEventTypes(): array;
    public function handleEvent(EventInterface $event): EventInterface;
}
