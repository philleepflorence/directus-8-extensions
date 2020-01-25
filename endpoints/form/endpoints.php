<?php

namespace Directus\Custom\Endpoints\Form;

require __DIR__ . '/controllers/Mail.php';
require __DIR__ . '/controllers/Submit.php';

return [
    '/mail' => [
        'method' => 'POST',
        'handler' => Mail::class
    ],
    '/submit' => [
        'method' => 'POST',
        'handler' => Submit::class
    ]
];
