<?php

namespace Directus\Custom\Endpoints\Search;

require __DIR__ . '/controllers/Build.php';
require __DIR__ . '/controllers/Cache.php';
require __DIR__ . '/controllers/Database.php';

return [
    '/build' => [
        'method' => 'GET',
        'handler' => Build::class
    ],
    '/cache' => [
        'method' => 'GET',
        'handler' => Cache::class
    ],
    '/database' => [
        'method' => 'GET',
        'handler' => Database::class
    ]
];
