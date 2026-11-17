<?php

declare(strict_types=1);

namespace Drongo\Module\Sales;

use Drongo\Core\Kernel;

/**
 * Sales module public entry point.
 *
 * Sales NEVER imports Drongo\Module\Inventory\* classes directly.
 * It receives the InventoryServiceInterface through constructor injection
 * (wired by the app layer) and talks to Inventory only via that interface.
 */
class SalesModule
{
    public static function register(Kernel $kernel): void
    {
        $kernel->registerModule('sales', [
            'displayName' => 'Sales',
            'ownerTeam' => '@drongo-org/sales-module',
            'dbSchema' => 'sal_',
            'dependsOn' => ['inventory'],
        ]);
    }

    public static function boot(Kernel $kernel): void
    {
        // No cross-module imports here. Boot is local to Sales only.
    }
}