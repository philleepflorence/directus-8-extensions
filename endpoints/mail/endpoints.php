<?php

namespace Directus\Custom\Endpoints\Mail;

require __DIR__ . '/controllers/Browser.php';

return [
    '/browser' => [
        'method' => 'GET',
        'handler' => Browser::class
    ]
];
