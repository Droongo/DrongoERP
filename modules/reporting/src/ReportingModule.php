<?php

declare(strict_types=1);

namespace Drongo\Module\Reporting;

use Drongo\Core\Kernel;
use Drongo\Module\Inventory\Events\StockAdjusted;
use Drongo\Module\Sales\Events\OrderPlaced;

/**
 * Reporting module.
 *
 * Reporting never calls Sales or Inventory services directly. It subscribes
 * to their domain events and builds its own read tables (rpt_*).
 */
class ReportingModule
{
    public static function register(Kernel $kernel): void
    {
        $kernel->registerModule('reporting', [
            'displayName' => 'Reporting',
            'ownerTeam' => '@Droongo/reporting-module',
            'dbSchema' => 'rpt_',
            'dependsOn' => ['sales', 'inventory'],
        ]);
    }

    public static function boot(Kernel $kernel): void
    {
        $kernel->on(OrderPlaced::class, [new SalesEventListener(), 'handle']);
        $kernel->on(StockAdjusted::class, [new InventoryEventListener(), 'handle']);
    }
}