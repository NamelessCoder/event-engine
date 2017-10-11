<?php
declare(strict_types=1);
namespace NamelessCoder\EventEngine;

interface EventTypeInterface
{
    const ANY_EVENT_TYPE = 42;
    public static function getAllNames(): array;
    public static function cast(string $type): EventTypeInterface;
    public function __toString(): string;
    public function getName(): string;
}
