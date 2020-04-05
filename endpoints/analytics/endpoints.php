<?php

namespace Directus\Custom\Endpoints\Analytics;

require __DIR__ . '/controllers/Application.php';
require __DIR__ . '/controllers/Dashboard.php';
require __DIR__ . '/controllers/Modules.php';
require __DIR__ . '/controllers/Set.php';

return [
    '/application' => [
        'method' => 'GET',
        'handler' => Application::class
    ],
    '/dashboard' => [
        'method' => 'GET',
        'handler' => Dashboard::class
    ],
    '/modules' => [
        'method' => 'GET',
        'handler' => Modules::class
    ],
    '/set' => [
        'method' => 'POST',
        'handler' => Set::class
    ]
];
