<?php
/**
 * Example mini-application implementing EventEngine
 *
 * From this example you can learn how to:
 *
 * - Declare event types for your application
 * - Dispatch the events from within your application
 * - Utilise the events to allow event handlers to modify data
 * - Use events as simple "the application did this action" events
 * - Dispatch pairs of events, e.g. start and stop, and using those
 *
 * The application is intentionally created in an excessively
 * simple way. All it does is dispatch events that are then
 * handled by the MyEventHandler and outputs data and info about
 * how the event was processed.
 *
 * Each of the example implementations loaded below are fully
 * documented and annotated and can be studied for reference.
 */

require_once __DIR__ . '/../vendor/autoload.php';

// Note, the loading order here is significant and you should be using composer to load classes.
// They are loaded manually here to avoid including them as autoloadable classes.
require_once 'MyEventType.php';
require_once 'MyEventHandler.php';


/*
 * The object instances we will now create:
 *
 * 1) An instance of the EventHandler we included
 * 2) An instance of the generic EventDispatcher from EventEngine
 *
 * The EventHandler is responsible for defining which events
 * it will handle, as well as doing the actual handling of the event.
 *
 * The EventDispatcher is, unsurprisingly, used when we need to dispatch
 * a new Event. We register our EventHandler with the dispatcher which
 * then calls on it when dispatching an Event of a type it handles.
 */
$eventHandler = new MyEventHandler();
$eventDispatcher = new \NamelessCoder\EventEngine\EventDispatcher();
$eventDispatcher->addHandler($eventHandler);

// Force text/plain for browser views (has no effect on CLI) to make output same as on CLI.
header('Content-Type: text/plain');

echo PHP_EOL;
echo '============ EventEngine DEMO APPLICATION ============' . PHP_EOL;
echo PHP_EOL;
echo '* Application is starting' . PHP_EOL;

// Create a fake request hash; to get something to pass to our first event
$requestHash = sha1(rand(100000, 999999));

// Dispatch our first event: our application is starting. Our EventHandler will output a
// string with the application start time in date/hour/minute precision.
// We keep the event here in a variable so we can use it in the end of our application and
// produce an "application took XYZ milliseconds" message at the end, also via an Event.
$applicationStartedEvent = $eventDispatcher->dispatch(
    $eventDispatcher->create(
        MyEventType::cast(MyEventType::APPLICATION_STARTED),
        new \NamelessCoder\EventEngine\EventData(['requestHash' => $requestHash])
    )
);

// We now emulate a "before" and "after" type event to fetch some data. The Event has
// a "condition" (hugely simplified, could be a set of filters for the fetching) and
// we redeclare our $condition variable using the dispatched event, to read any
// changes that may have been done by an EventHandler. In this case: our EventHandler
// changes the condition from "true" to "false".
$condition = true;
echo '* Application thinks it will emulate data fetching with condition "' . ($condition ? 'true' : 'false') . PHP_EOL;
$beforeDataFetchEvent = $eventDispatcher->dispatch(
    $eventDispatcher->create(
        MyEventType::cast(MyEventType::DATA_FETCH_INIT),
        new \NamelessCoder\EventEngine\EventData(['condition' => $condition])
    )
);
$condition = $beforeDataFetchEvent->getData()->offsetGet('condition');

// Make our application report it will load data with condition "true":
echo '* Application will now emulate data fetching with condition "' . ($condition ? 'true' : 'false') . PHP_EOL;

// Emulate passing a tiny bit of random time:
usleep(rand(100000, 500000));

// Emulate some data that was fetched, could for example be SQL records or domain objects
$fetchedData = [
    'foo' => 'bar'
];

// Dispatch the "after" data fetch event, passing the data that was fetched. Our
// EventHandler will add a second entry to the data. We call our fetched data
// "records" in the data but could have used any name we wanted.
$afterDataFetchEvent = $eventDispatcher->dispatch(
    $eventDispatcher->create(
        MyEventType::cast(MyEventType::DATA_FETCH_COMPLETE),
        new \NamelessCoder\EventEngine\EventData(['condition' => $condition, 'records' => $fetchedData]),
        $beforeDataFetchEvent
    )
);

echo '* Application fetched data: ' . json_encode($afterDataFetchEvent->getData()->offsetGet('records')) . PHP_EOL;

// Our application reports the time it took - but this could also be done by the
// EventHandler, like it gets done with the main application execution time below.
echo '* Application took ' . number_format($afterDataFetchEvent->getDuration(), 2) . ' milliseconds to fetch data' . PHP_EOL;


// Emulate passing a tiny bit of random time:
usleep(rand(100000, 200000));

// We now fire a one-off, unpaired event that just says our application did something.
// The "application did output" is just an example of something that might happen in
// your average application.
$contentToOutput = '"This output comes from Application"';
echo $contentToOutput . PHP_EOL;

// Dispatch the event informing that we did output. The EventHandler will then output
// how many bytes the Application sent. Alternatively, if your application waited
// until this Event was dispatched and then read the "content" back from it, this would
// allow the EventDispatcher to also change the content itself, before it got output.
// But in this case we don't use the Event afterwards - we fire and forget.
$eventDispatcher->dispatch(
    $eventDispatcher->create(
        MyEventType::cast(MyEventType::APPLICATION_DID_OUTPUT),
        new \NamelessCoder\EventEngine\EventData(['content' => $contentToOutput])
    )
);

echo '* Application will now exit' . PHP_EOL;

// Our last event of the evening: the application termination event. Our EventHandler
// catches this event and outputs the time it took between the application started
// Event and this final Event. In this case the Event has no data so we pass null.
$eventDispatcher->dispatch(
    $eventDispatcher->create(
        MyEventType::cast(MyEventType::APPLICATION_FINISHED),
        null,
        $applicationStartedEvent
    )
);

echo PHP_EOL;
