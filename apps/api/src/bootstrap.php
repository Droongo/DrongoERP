<?php

declare(strict_types=1);

namespace Drongo\App\Api;

use Drongo\Core\Kernel;
use Drongo\Module\Accounting\AccountingModule;
use Drongo\Module\Inventory\InventoryModule;
use Drongo\Module\Inventory\StockService;
use Drongo\Module\Reporting\ReportingModule;
use Drongo\Module\Sales\OrderService;
use Drongo\Module\Sales\SalesModule;

require_once __DIR__ . '/../../../vendor/autoload.php';

$kernel = new Kernel();

// --- Register every enabled module -----------------------------------------
AccountingModule::register($kernel);
InventoryModule::register($kernel);
SalesModule::register($kernel);
ReportingModule::register($kernel);

// --- Wire cross-module services (APP layer only) ---------------------------
// Modules stay decoupled: the app is the only place that knows both Sales
// and Inventory. Sales receives Inventory's public interface via injection,
// never by importing an Inventory concrete class.
$kernel->setService(
    'sales.order',
    new OrderService(
        $kernel,
        $kernel->getService('inventory.stock')
    )
);

// --- Boot modules ----------------------------------------------------------
AccountingModule::boot($kernel);
InventoryModule::boot($kernel);
SalesModule::boot($kernel);
ReportingModule::boot($kernel);

$kernel->emit('app.booted', ['kernel' => $kernel]);