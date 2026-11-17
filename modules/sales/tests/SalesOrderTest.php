<?php

declare(strict_types=1);

namespace Drongo\Module\Sales\Tests;

use Drongo\Module\Sales\OrderService;
use PHPUnit\Framework\TestCase;

class StubInventory implements \Drongo\Core\Contracts\InventoryServiceInterface
{
    public function reserveStock(array $items): array
    {
        return ['ok' => true, 'reservation_id' => 'res_123', 'message' => 'ok'];
    }

    public function releaseStock(string $reservationId): void
    {
        // no-op
    }
}

class SalesOrderTest extends TestCase
{
    public function testOrderIsPlacedWhenStockAvailable(): void
    {
        $kernel = new \Drongo\Core\Kernel();
        $service = new OrderService($kernel, new StubInventory());

        $result = $service->place([
            'customer_id' => 'cust_1',
            'items' => [['sku' => 'SKU-1', 'qty' => 2]],
            'total' => 100.00,
        ]);

        $this->assertSame('placed', $result['status']);
        $this->assertStringStartsWith('sal_', $result['order_id']);
    }
}