<?php

namespace Directus\Custom\Endpoints\Database;

require __DIR__ . '/controllers/Archive.php';
require __DIR__ . '/controllers/Backup.php';
require __DIR__ . '/controllers/Collection.php';
require __DIR__ . '/controllers/Field.php';
require __DIR__ . '/controllers/Migrate.php';
require __DIR__ . '/controllers/Schema.php';

return [
    '/archive' => [
        'method' => 'GET',
        'handler' => Archive::class
    ],
    '/backup' => [
        'method' => 'GET',
        'handler' => Backup::class
    ],
    '/collection' => [
        'method' => 'PATCH',
        'handler' => Collection::class
    ],
    '/field' => [
        'method' => 'PATCH',
        'handler' => Field::class
    ],
    '/migrate' => [
        'method' => 'GET',
        'handler' => Migrate::class
    ],
    '/schema' => [
        'method' => 'GET',
        'handler' => Schema::class
    ]
];
