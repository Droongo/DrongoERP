<?php

declare(strict_types=1);

namespace Drongo\Module\Reporting\Tests;

use Drongo\Module\Reporting\EventListeners\InventoryEventListener;
use Drongo\Module\Reporting\EventListeners\SalesEventListener;
use PHPUnit\Framework\TestCase;

class ReportingListenerTest extends TestCase
{
    public function testSalesListenerHandlesOrderPlaced(): void
    {
        $listener = new SalesEventListener();
        $this->assertNull($listener->handle([
            'order_id' => 'sal_123',
            'total' => 99.99,
        ]));
    }

    public function testInventoryListenerHandlesStockAdjusted(): void
    {
        $listener = new InventoryEventListener();
        $this->assertNull($listener->handle([
            'sku' => 'SKU-1',
            'quantityChange' => -2,
            'reason' => 'reservation',
        ]));
    }
}