<?php

class MyEventHandler implements \NamelessCoder\EventEngine\EventHandlerInterface
{
    public function getHandledEventTypes(): array
    {
        return MyEventType::getAllNames();
    }

    public function handleEvent(\NamelessCoder\EventEngine\EventInterface $event): \NamelessCoder\EventEngine\EventInterface
    {
        switch ($event->getType()->getName()) {
            case MyEventType::APPLICATION_STARTED:
                echo '  - MyEventHandler detected Application started at: ' . date('Y-m-d H:i', $event->getCreationTime()) . PHP_EOL;
                break;
            case MyEventType::DATA_FETCH_INIT:
                echo '  - MyEventHandler is changing condition to "false"' . PHP_EOL;
                $event->getData()->offsetSet('condition', false);
                break;
            case MyEventType::DATA_FETCH_COMPLETE:
                echo '  - MyEventHandler is adding "baz" with value "example" to the EventData' . PHP_EOL;
                $event->getData()->offsetSet('records', array_merge($event->getData()->offsetGet('records'), ['baz' => 'example']));
                break;
            case MyEventType::APPLICATION_DID_OUTPUT:
                echo '  - MyEventHandler determined Application sent ' . strlen($event->getData()->offsetGet('content')) . ' byte(s) of some output' . PHP_EOL;
                break;
            case MyEventType::APPLICATION_FINISHED:
                echo '  - MyEventHandler determined the Application took ' . number_format($event->getDuration(), 2) . ' milliseconds to finish' . PHP_EOL;
                break;
            default:
                break;
        }

        // The default behavior here is to return the same Event we received. It is, however, perfectly
        // possible and valid to return a *different* Event - and if that is done, then that new Event
        // gets dispatched to any subsequent EventHandlers, and gets returned via the EventDispatcher.
        return $event;
    }
}