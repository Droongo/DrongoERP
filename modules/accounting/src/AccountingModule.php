<?php

declare(strict_types=1);

namespace Drongo\Module\Accounting;

use Drongo\Core\Kernel;

/**
 * Accounting module public entry point.
 *
 * This is the ONLY file other modules/apps should import from this module.
 * All internal classes live under Drongo\Module\Accounting\Internal\ and are
 * off-limits to the rest of the monorepo.
 */
class AccountingModule
{
    public static function register(Kernel $kernel): void
    {
        $kernel->registerModule('accounting', [
            'displayName' => 'Accounting',
            'version' => '1.0.0',
            'ownerTeam' => '@Droongo/accounting-module',
            'dbSchema' => 'acct_',
            'routes' => __DIR__ . '/../config/routes.php',
        ]);

        // Expose public services via the kernel, not via direct class coupling.
        $kernel->setService('accounting.ledger', new LedgerService());
    }

    public static function boot(Kernel $kernel): void
    {
        // Module boot logic (register routes, listeners, etc.)
    }
}