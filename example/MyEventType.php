<?php

class MyEventType implements \NamelessCoder\EventEngine\EventTypeInterface
{
    /**
     * The actual type names this "EventType" can have. Declared
     * as constants for enumeration and easy referencing.
     */
    const APPLICATION_STARTED = 'applicationStarted'; // First event of a pair
    const APPLICATION_FINISHED = 'applicationFinished'; // Second event of a pair
    const APPLICATION_DID_OUTPUT = 'applicationDidOutput'; // Single event not part of a pair
    const DATA_FETCH_INIT = 'dataFetchInit'; // First event of a pair
    const DATA_FETCH_COMPLETE = 'dataFetchComplete'; // Second event in the pair

    /**
     * The default type here is "Uninitialized" and will always
     * be overridden. In your implementation you may want your
     * event type to have a default type and not require the
     * constructor to specify the type - in which case using
     * a property like this is one way to go.
     *
     * @var string
     */
    private $type = 'Uninitialized';

    /**
     * Constructor is NOT specified in the interface for a
     * very good reason - this allows you to create individual
     * classes for each event and type, if this is what you
     * prefer for your application.
     *
     * In this example though, one general "MyEventType" class
     * is used, which can have several different types. We
     * guard the event type by checking if the type-name exists
     * in the array of valid event names - other implementations
     * can validate the type (or not!) in any way they choose.
     *
     * For convenience, EventEngine includes a specific exception
     * type you can throw when an invalid event type-name is
     * passed, so you can more closely report problems to devs
     * integrating with your application.
     *
     * See also the "cast()" method below.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (!in_array($type, static::getAllNames())) {
            throw new \NamelessCoder\EventEngine\InvalidEventTypeException(
                'Event type "' . $type . '" is unknown. Valid types: ' . implode(', ', static::getAllNames())
            );
        }
        $this->type = $type;
    }

    /**
     * Cheating with enumeration: analyze all constants
     * defined on this class and return their values, which
     * will be the valid name-values of events.
     *
     * Usually you want to either hardcode these or cache
     * the result of the reflection, but for development
     * purposes this shortcut that automatically adds any
     * new constants is a great help.
     *
     * @return string[]
     */
    public static function getAllNames(): array
    {
        return array_values((new \ReflectionClass(static::class))->getConstants());
    }

    /**
     * Casting a string value of the enumerated constants.
     * In this example we do this by simply passing the
     * value to the constructor which we created so it takes
     * just the type as argument.
     *
     * The reason for having "cast()" on the EventType itself
     * and not leaving this up to the application, is exactly
     * the ability to make one class serve as both instance
     * and enumeration of event types.
     *
     * Note that this method can indeed also be called on any
     * implementation class and due to the late static binding
     * will return an instance of that class, so the "cast()"
     * method below can be implemented on for example a shared
     * parent class for your event types, should you choose to
     * split each type into a separate class or create smaller
     * chunks to group event types by their logical relation.
     *
     * @param string $type
     * @return \NamelessCoder\EventEngine\EventTypeInterface
     */
    public static function cast(string $type): \NamelessCoder\EventEngine\EventTypeInterface
    {
        return new static($type);
    }

    /**
     * Very standard toString() method - must return the type
     * as name-value which is set in $this->type when the
     * instance is constructed.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->type;
    }

    /**
     * Basically the same as __toString() but required by the
     * interface and added here for two primary reasons:
     *
     * 1) This is an explicit way to read the type-name of an
     *    event type that doesn't require string-casting.
     * 2) The getter method is a standard pattern that many
     *    frameworks can access automatically, which is very
     *    nice in particular for event types and distinguishes
     *    access to the name, from access to the object.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->type;
    }
}