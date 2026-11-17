<?php

declare(strict_types=1);

namespace Drongo\Module\Inventory\Events;

use Drongo\Core\Events\DomainEvent;

class StockAdjusted extends DomainEvent
{
    public function __construct(
        public readonly string $sku,
        public readonly int $quantityChange,
        public readonly string $reason,
    ) {
        parent::__construct();
    }
}