<?php

declare(strict_types=1);

namespace Drongo\Module\Inventory;

use Drongo\Core\Contracts\InventoryServiceInterface;
use Drongo\Core\Kernel;
use Drongo\Module\Inventory\Events\StockAdjusted;

/**
 * Public implementation of Drongo\Core\Contracts\InventoryServiceInterface.
 *
 * Sales depends on this interface (defined in packages/core), not on this
 * concrete class. That keeps Sales decoupled from Inventory internals.
 */
class StockService implements InventoryServiceInterface
{
    public function __construct(protected Kernel $kernel) {}

    public function reserveStock(array $items): array
    {
        foreach ($items as $item) {
            // Decrement inv_* tables here. Simulated for the example.
        }

        // Notify other modules via event — no direct calls.
        $this->kernel->emit(StockAdjusted::class, [
            'sku' => $items[0]['sku'] ?? 'unknown',
            'quantityChange' => -array_sum(array_column($items, 'qty')),
            'reason' => 'reservation',
        ]);

        return [
            'ok' => true,
            'reservation_id' => 'res_' . uniqid(),
            'message' => 'stock reserved',
        ];
    }

    public function releaseStock(string $reservationId): void
    {
        // Restore inv_* tables and emit StockAdjusted with positive quantity.
    }
}