<?php

namespace Directus\Custom\Endpoints\Files;

require __DIR__ . '/controllers/Unlink.php';

return [
    '/unlink' => [
        'method' => 'GET',
        'handler' => Unlink::class
    ]
];
