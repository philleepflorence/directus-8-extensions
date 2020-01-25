<?php

namespace Directus\Custom\Endpoints\Collections;

require __DIR__ . '/controllers/Compile.php';
require __DIR__ . '/controllers/Migrate.php';

return [
    '/compile' => [
        'method' => 'GET',
        'handler' => Compile::class
    ],
    '/migrate' => [
        'method' => 'POST',
        'handler' => Migrate::class
    ]
];
