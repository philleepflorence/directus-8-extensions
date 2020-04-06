<?php

namespace Directus\Custom\Endpoints\Notifications;

require __DIR__ . '/controllers/Push.php';

return [
    '/push' => [
        'method' => 'POST',
        'handler' => Push::class
    ]
];
