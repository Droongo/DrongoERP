<?php

declare(strict_types=1);

namespace Drongo\Module\Accounting;

/**
 * Public service. Consumers access this through Kernel::getService('accounting.ledger').
 */
class LedgerService
{
    public function postEntry(array $entry): array
    {
        return [
            'status' => 'posted',
            'entry' => $entry,
            'module' => 'accounting',
        ];
    }
}