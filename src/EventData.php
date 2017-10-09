<?php
declare(strict_types=1);
namespace NamelessCoder\EventEngine;

class EventData implements EventDataInterface
{
    private $data;

    private $valid = true;

    public function current()
    {
        return current($this->data);
    }

    public function next()
    {
        return ($candidate = next($this->data) !== false) ? $candidate : $this->valid = false;
    }

    public function key()
    {
        return key($this->data);
    }

    public function valid(): bool
    {
        return $this->valid;
    }

    public function rewind()
    {
        $this->valid = (bool) count($this->data);
        return reset($this->data);
    }

    public function offsetExists($offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    public function offsetGet($offset)
    {
        return $this->data[$offset] ?? null;
    }

    public function offsetSet($offset, $value)
    {
        $this->data[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->data[$offset]);
    }

    public function __construct(array $data = [])
    {
        $this->data = $data;
        $this->valid = (bool) count($data);
    }

    public function has(string $property): bool
    {
        return $this->offsetExists($property);
    }

    public function add(string $property, $value): EventDataInterface
    {
        $this->data[$property] = $value;
        return $this;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
