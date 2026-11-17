<?php

declare(strict_types=1);

namespace Drongo\Module\Sales\Events;

use Drongo\Core\Events\DomainEvent;

class OrderPlaced extends DomainEvent
{
    public function __construct(
        public readonly string $orderId,
        public readonly string $customerId,
        public readonly array $items,
        public readonly float $total,
        public readonly ?string $reservationId = null,
    ) {
        parent::__construct();
    }
}