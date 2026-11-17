<?php

declare(strict_types=1);

namespace Drongo\Module\Inventory\Tests;

use Drongo\Module\Inventory\StockService;
use PHPUnit\Framework\TestCase;

class StubKernel
{
    public array $emitted = [];

    public function emit(string $event, array $payload = []): void
    {
        $this->emitted[] = [$event, $payload];
    }

    public function getService(string $name): ?object
    {
        return null;
    }

    public function setService(string $name, object $service): void
    {
        // no-op
    }
}

class StockServiceTest extends TestCase
{
    public function testReserveStockEmitsEvent(): void
    {
        $kernel = new StubKernel();
        $service = new StockService($kernel);

        $result = $service->reserveStock([
            ['sku' => 'SKU-1', 'qty' => 2],
        ]);

        $this->assertTrue($result['ok']);
        $this->assertCount(1, $kernel->emitted);
        $this->assertSame('SKU-1', $kernel->emitted[0][1]['sku']);
    }
}