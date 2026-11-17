<?php

declare(strict_types=1);

namespace Drongo\Core\Events;

/**
 * Base class for all domain events published through the Kernel.
 *
 * Modules publish events; other modules subscribe. No direct calls between
 * modules are required.
 */
abstract class DomainEvent
{
    public readonly string $name;
    public readonly \DateTimeImmutable $occurredAt;

    public function __construct()
    {
        $this->name = static::class;
        $this->occurredAt = new \DateTimeImmutable();
    }
}