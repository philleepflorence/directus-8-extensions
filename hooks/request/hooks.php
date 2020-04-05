<?php

use Directus\Custom\Helpers\Cache;
use Directus\Custom\Helpers\Request;

use Directus\Util\ArrayUtils;

require_once dirname(dirname(__DIR__)) . '/helpers/functions.php';

return [
    'actions' => [
        'application.boot' => function ($app) 
        {
	        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	        
	        Request::Headers();
	        
	        if (strpos($path, 'auth/logout')) Cache::Session();	
	        
	        $url = Request::Filters();
	        
	        if ($url) 
	        {
		        header("Location: {$url}");
		        die();
	        }   
        }
    ]
];
