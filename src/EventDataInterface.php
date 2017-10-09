<?php
declare(strict_types=1);
namespace NamelessCoder\EventEngine;

interface EventDataInterface extends \Iterator, \ArrayAccess
{
    public function has(string $property): bool;
    public function add(string $property, $value): EventDataInterface;
    public function toArray(): array;
}
