<?php

namespace Directus\Custom\Endpoints\Files;

require __DIR__ . '/controllers/Migrate.php';
require __DIR__ . '/controllers/Unlink.php';

return [
	'/migrate' => [
        'method' => 'GET',
        'handler' => Migrate::class
    ],
    '/unlink' => [
        'method' => 'GET',
        'handler' => Unlink::class
    ]
];
