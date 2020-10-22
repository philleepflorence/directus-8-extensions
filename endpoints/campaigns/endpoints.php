<?php

namespace Directus\Custom\Endpoints\Campaigns;

require __DIR__ . '/controllers/Send.php';

return [
    '/send' => [
        'method' => 'GET',
        'handler' => Send::class
    ]
];
