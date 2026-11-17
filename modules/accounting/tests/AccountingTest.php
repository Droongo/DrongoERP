<?php

declare(strict_types=1);

namespace Drongo\Module\Accounting\Tests;

use Drongo\Module\Accounting\LedgerService;
use PHPUnit\Framework\TestCase;

class AccountingTest extends TestCase
{
    public function testLedgerPostsEntry(): void
    {
        $ledger = new LedgerService();
        $result = $ledger->postEntry(['account' => '1000', 'amount' => 100.00]);

        $this->assertSame('posted', $result['status']);
        $this->assertSame('accounting', $result['module']);
    }
}