EventEngine: An engine for integrating Events in your application
=================================================================

[![Build Status](https://travis-ci.org/NamelessCoder/event-engine.svg?branch=master)](https://travis-ci.org/NamelessCoder/event-engine)

Purpose: to provide a clean and strict set of interfaces and solid default implementations around the concept of
"Events", meaning "things that happen in your code which other code can listen for and react to".

Events, Hooks, Signals - a dear child has many names, as we say in Danish. The concept for this Event package comes
from a long and proud history in the TYPO3 CMS and takes inspiration from other event-based implementations. It is
designed to allow a vital set of capabilities:

1. Dispatching events, obviously.
2. Being able to pair events in for example "start" and "stop" event pairs for a particular action.
3. Being able to report data (payload) from within your application to third party code that wants it.
4. If so wanted, allow third party code to modify the data of an event and use the modified data in the continuation
   of your application.
5. Enabling one handler to handle several types of events as well as giving the handler the responsibility to report
   which event types it will listen to (thus allowing this to be dynamically decided in implementations).

Together these parts form a flexible and quite powerful framework that makes it easy to implement Events in your
application - and use those as a universal API for any integrations third parties need to make with your application.


How does it work
----------------

I suggest you take a quick look at the example application in the `examples` folder. This tiny application uses all the
essential parts of the EventEngine and is thoroughly documented with comments in the code.

The example application is executable - you can run it on the command line or you can serve it with a httpd. It is
however *completely non-interactive*; it takes absolutely no input arguments from anywhere.


A note about the time tracking capabilities
-------------------------------------------

While it can be argued that tracking time with Events is not exactly within the purview of an Events library, adding it
at this low level has significant benefits. First of all, the time tracking is extremely cheap to do. Second, an often
seen use case for instrumentation based on Events involves tracking the time between events - either for profiling
purposes, for logging of "slow actions", or other similar cases.

So time tracking is included with this library in the form of a couple of methods and a concept of "paired events".


A note about paired events
--------------------------

This concept comes more or less directly from several "before" and "after" style hooks in TYPO3 CMS. The idea is simply
that some hooks need to trigger before an action occurs - others need to trigger after. Each then receives different
data, e.g. the "after" hook receives what was built by the application between that and the "before" hook.

There are no declared rules for naming which implies a hook is a "before" or "after" hook - this is solely determined
by whether or not the Event has an "initiating event" (which is itself an Event).

This means that when an Event has an "initiating event" and both those Events have a creation timestamp, it is possible
to easily calculate the duration between the initiating event and the second even in the pair. And because the
EventDispatcher returns the event it has dispatched, it becomes very easy to implement this type of "before" and "after"
paired Events. Consider the following example:

```php
$startEvent = $eventDispather->dispatch(
    new Event(
        MyEventType::cast(MyEventType::MY_EVENT),
        new EventData(['foo' => 'bar'])
    )
);

// Application now does something that takes a bit of time

$endEvent = $eventDispatcher->dispatch(
    new Event(
        MyEventType::cast(MyEventType::MY_EVENT_FINISHED),
        new EventData(['foo' => 'bar']),
        $startEvent
    )
);

// Meanwhile, every handler that is capable of handling the
// "finish" Event will have received an Event which has a duration.
// EventHandlers may read this duration - but so may your application:

$logger->doLogging('Doing action XYZ took ' . number_format($endEvent->getDuration(), 3)  . ' milliseconds'); 
```

So you get three features in one by dispatching before and after events:

1. The obvious benefit of letting third parties listen for events and handle them, and tracking time if they want.
2. The secondary benefit that your application can also read the duration between the first and second paired Events.
3. The tertiary benefit that EventHandlers can, using one type of Event, compare the data in the before and after Events
   by simply accessing the data of the initiating Event via the second Event in the pair.

And it literally only costs two calls to `microtime(true)` and one subtraction calculation to track all this, which is
why it is part of the library itself.


What does it NOT do?
--------------------

The package is built with KISS and YAGNI in mind (Keep It Simple, Stupid - and You Aren't Going to Need It) which means
it has no bells and whistles such as prioritization of event handlers, dependency-based ordering of handlers, various
special implementations of Events and built-in EventTypes. The package has none of that.

What it does have is a base library that is flexible enough to allow you to create your own implementations of all of
these things with any number of special features you desire.

In short, this package exists mainly to provide interfaces; contracts for all things related to Events and only Events.
It does ship with generic implementations for the interfaces, but they are intentionally kept to an absolute minimum
complexity with just enough capabilities to fulfil the contracts.

Important:

* The package *does not include a default EventHandler* - along with an event type (enumeration object), these are the
  two components you *must* build for your application, or find as separate package you can use.
* The package declares *zero event types* and there will be nothing to dispatch unless you declare these or add a
  package that does.


A (very) brief history
----------------------

The concept of "Events" is neither ground-breaking nor unique to TYPO3 CMS. In fact, it is by now an industry standard
way and is widely known from the JavaScript world since... well, since forever.

But to explain why the EventEngine package does what it does, a brief history of TYPO3 CMS in terms of third party code
integrations is in order:

* The very first iteration of "user functions" that could be configured to be called in various circumstances such as
  returning a simple string value for use in HTML output, came very early - almost with the very first version of TYPO3.
  While such methods are good in that they are explicit and easy to define, they lack flexibility because only one may
  be configured at any time.
* Then came "hooks" which are almost self-explanatory: rather than declaring one function, it became possible to declare
  an array of function references which would all be called, in sequence, when a certain action occurred in the system.
  This concept made it possible for third party code to *change* the data TYPO3 CMS would use in the continuation of
  the application - for example, allowing a content element's title to be changed on the fly when previewed.
* Later came the concept of "signals" which originally were added as a backported version of a PHP framework then called
  Flow and now known as Neos. Although TYPO3 CMS already had the concept of "hooks", Flow/Neos needed a stricter
  implementation and created "signals" which contrary to the more basic arrays of function references, had a way to
  subscribe to signals by referring to the class which dispatches them and a name of the signal.

Today, all three of these concepts still exist in TYPO3 CMS and it isn't hard to argue that they should be unified into
a single implementation that serves all of the very similar purposes. However, due to the extremely ingrained support
for "user functions" which in TYPO3 CMS are used in nearly all aspects of TypoScript in particular, the EventEngine is
not intended to replace this pattern - although it can be used with it, to replace what's currently "user functions" and
turn them into dispatched events that allow third parties to receive, modify and pass data back to TYPO3.

