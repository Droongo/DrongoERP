<?php

declare(strict_types=1);

namespace Drongo\Module\Sales;

use Drongo\Core\Contracts\InventoryServiceInterface;
use Drongo\Core\Kernel;
use Drongo\Module\Sales\Events\OrderPlaced;

/**
 * Sales places an order and reserves stock through Inventory's public
 * interface. The concrete implementation is injected by the app layer.
 */
class OrderService
{
    public function __construct(
        protected Kernel $kernel,
        protected InventoryServiceInterface $inventory
    ) {}

    public function place(array $orderData): array
    {
        // 1. Reserve stock via Inventory public contract (declared dependency)
        $reservation = $this->inventory->reserveStock($orderData['items'] ?? []);

        if (!$reservation['ok']) {
            return [
                'status' => 'rejected',
                'reason' => 'insufficient_stock',
                'reservation_id' => null,
            ];
        }

        // 2. Persist to Sales-owned tables only (sal_* schema)
        $orderId = 'sal_' . uniqid();

        // 3. Publish an event so Reporting and other modules can react.
        // Sales does NOT call Reporting directly.
        $this->kernel->emit(OrderPlaced::class, [
            'order_id' => $orderId,
            'customer_id' => $orderData['customer_id'] ?? null,
            'items' => $orderData['items'] ?? [],
            'total' => $orderData['total'] ?? 0.0,
            'reservation_id' => $reservation['reservation_id'] ?? null,
        ]);

        return [
            'status' => 'placed',
            'order_id' => $orderId,
            'reservation_id' => $reservation['reservation_id'] ?? null,
        ];
    }

    public function cancel(string $orderId): void
    {
        // Example: release stock back through Inventory's public contract.
        // In a real app you'd look up the reservation_id from your own tables.
    }
}