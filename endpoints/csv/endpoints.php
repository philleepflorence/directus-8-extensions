<?php

namespace Directus\Custom\Endpoints\CSV;

require __DIR__ . '/controllers/Export.php';
require __DIR__ . '/controllers/Import.php';

return [
    '/export' => [
        'method' => 'POST',
        'handler' => Export::class
    ],
    '/import' => [
        'method' => 'POST',
        'handler' => Import::class
    ]
];
