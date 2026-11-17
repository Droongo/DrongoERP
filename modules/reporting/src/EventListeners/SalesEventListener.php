<?php

declare(strict_types=1);

namespace Drongo\Module\Reporting\EventListeners;

use Drongo\Core\Events\EventSubscriberInterface;
use Drongo\Module\Sales\Events\OrderPlaced;

class SalesEventListener implements EventSubscriberInterface
{
    public function handle(array $payload): void
    {
        // Write into Reporting's own tables (rpt_*). Never writes to sal_* or inv_*.
        $orderId = $payload['order_id'] ?? 'unknown';
        $total = $payload['total'] ?? 0.0;

        // Simulated insert into rpt_daily_sales
        error_log(sprintf(
            'REPORTING: recorded sale order=%s total=%.2f',
            $orderId,
            $total
        ));
    }
}