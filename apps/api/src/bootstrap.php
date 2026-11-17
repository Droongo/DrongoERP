<?php

declare(strict_types=1);

namespace Drongo\App\Api;

use Drongo\Core\Kernel;
use Drongo\Module\Accounting\AccountingModule;

require_once __DIR__ . '/../../../vendor/autoload.php';

$kernel = new Kernel();

// Register every enabled module here. Adding a new module means editing THIS
// file and adding its composer.json to the root merge-plugin include list.
AccountingModule::register($kernel);

$kernel->emit('app.booted', ['kernel' => $kernel]);