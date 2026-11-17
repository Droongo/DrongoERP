<?php

declare(strict_types=1);

namespace Drongo\Module\Reporting\EventListeners;

use Drongo\Core\Events\EventSubscriberInterface;
use Drongo\Module\Inventory\Events\StockAdjusted;

class InventoryEventListener implements EventSubscriberInterface
{
    public function handle(array $payload): void
    {
        // Write into Reporting's own tables (rpt_*). Never writes to inv_*.
        $sku = $payload['sku'] ?? 'unknown';
        $change = $payload['quantityChange'] ?? 0;

        // Simulated insert into rpt_stock_movements
        error_log(sprintf(
            'REPORTING: stock movement sku=%s change=%d reason=%s',
            $sku,
            $change,
            $payload['reason'] ?? 'unknown'
        ));
    }
}