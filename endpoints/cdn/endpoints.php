<?php

namespace Directus\Custom\Endpoints\CDN;

require __DIR__ . '/controllers/Files.php';

return [
    '/files' => [
        'method' => 'GET',
        'handler' => Files::class
    ]
];
