<?php

namespace Directus\Custom\Endpoints\Thumbnailer;

require __DIR__ . '/controllers/Sizes.php';

return [
    '/sizes' => [
        'method' => 'GET',
        'handler' => Sizes::class
    ]
];
