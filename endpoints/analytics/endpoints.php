<?php

namespace Directus\Custom\Endpoints\Analytics;

require __DIR__ . '/controllers/Analytics.php';

return [
    '/set' => [
        'method' => 'POST',
        'handler' => Analytics::class
    ]
];
