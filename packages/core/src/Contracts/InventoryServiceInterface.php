<?php

declare(strict_types=1);

namespace Drongo\Core\Contracts;

/**
 * Shared cross-module contract.
 *
 * Modules that need inventory (e.g. Sales) depend on THIS interface in
 * packages/core, NOT on Drongo\Module\Inventory\* concrete classes.
 * The Inventory module provides the implementation.
 */
interface InventoryServiceInterface
{
    /**
     * Reserve stock for a list of items.
     *
     * @return ['ok' => bool, 'reservation_id' => string|null, 'message' => string]
     */
    public function reserveStock(array $items): array;

    public function releaseStock(string $reservationId): void;
}