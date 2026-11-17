<?php

declare(strict_types=1);

return [
    'prefix' => '/api/v1/accounting',
    'routes' => [
        ['method' => 'GET',    'path' => '/ledger',          'handler' => 'AccountingController@index'],
        ['method' => 'POST',   'path' => '/ledger/entry',    'handler' => 'AccountingController@postEntry'],
    ],
];