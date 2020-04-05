<?php

namespace Directus\Custom\Endpoints\Curl;

require __DIR__ . '/controllers/Content.php';

return [
    '/content' => [
        'method' => 'GET',
        'handler' => Content::class
    ]
];
