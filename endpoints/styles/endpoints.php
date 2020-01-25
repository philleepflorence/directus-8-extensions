<?php

namespace Directus\Custom\Endpoints\Styles;

require __DIR__ . '/controllers/Variables.php';

return [
    '/variables' => [
        'method' => 'GET',
        'handler' => Variables::class
    ]
];
