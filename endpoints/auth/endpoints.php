<?php

namespace Directus\Custom\Endpoints\Auth;

require __DIR__ . '/controllers/Confirm.php';
require __DIR__ . '/controllers/Credentials.php';
require __DIR__ . '/controllers/Login.php';
require __DIR__ . '/controllers/Logout.php';
require __DIR__ . '/controllers/Reset.php';
require __DIR__ . '/controllers/Register.php';

return [
    '/confirm' => [
        'method' => 'POST',
        'handler' => Confirm::class
    ],
    '/credentials' => [
        'method' => 'POST',
        'handler' => Credentials::class
    ],
    '/login' => [
        'method' => 'POST',
        'handler' => Login::class
    ],
    '/logout' => [
        'method' => 'POST',
        'handler' => Logout::class
    ],
    '/reset' => [
        'method' => 'POST',
        'handler' => Reset::class
    ],
    '/register' => [
        'method' => 'POST',
        'handler' => Register::class
    ]
];
