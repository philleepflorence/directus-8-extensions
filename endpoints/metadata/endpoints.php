<?php

namespace Directus\Custom\Endpoints\Metadata;

require __DIR__ . '/controllers/Import.php';

return [
    '/import' => [
        'method' => 'GET',
        'handler' => Import::class
    ]
];
