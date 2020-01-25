<?php

namespace Directus\Custom\Endpoints\User;

require __DIR__ . '/controllers/Metadata.php';
require __DIR__ . '/controllers/Notifications.php';
require __DIR__ . '/controllers/Settings.php';

return [
    '/metadata' => [
        'method' => 'POST',
        'handler' => Metadata::class
    ],
    '/notifications' => [
        'method' => 'GET',
        'handler' => Notifications::class
    ],
    '/settings' => [
        'method' => 'POST',
        'handler' => Settings::class
    ]
];
