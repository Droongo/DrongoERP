<?php

declare(strict_types=1);

namespace Drongo\Core\Events;

/**
 * Optional marker interface for classes that handle domain events.
 */
interface EventSubscriberInterface
{
    public function handle(array $payload): void;
}