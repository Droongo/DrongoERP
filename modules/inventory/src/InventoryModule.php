<?php

declare(strict_types=1);

namespace Drongo\Module\Inventory;

use Drongo\Core\Kernel;

/**
 * Inventory module public entry point.
 *
 * Inventory has NO dependencies on other modules. It exposes StockService
 * through the Kernel and publishes StockAdjusted events for Reporting.
 */
class InventoryModule
{
    public static function register(Kernel $kernel): void
    {
        $kernel->registerModule('inventory', [
            'displayName' => 'Inventory',
            'ownerTeam' => '@drongo-org/inventory-module',
            'dbSchema' => 'inv_',
            'dependsOn' => [],
        ]);

        $kernel->setService('inventory.stock', new StockService());
    }

    public static function boot(Kernel $kernel): void
    {
        // Inventory only manages its own tables (inv_*). It never reaches
        // into Sales, Reporting, or any other module.
    }
}