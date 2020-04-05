<?php

use Directus\Custom\Helpers\Api;
use Directus\Custom\Helpers\FileSystem;

use Directus\Hook\Payload;
use Directus\Util\ArrayUtils;

require_once dirname(dirname(__DIR__)) . '/helpers/functions.php';

return [
    'actions' => [
        'file.save:after' => function ($file) 
        {
	        if (stripos($_SERVER['REQUEST_URI'], '/app/files') === false) FileSystem::Thumbnailer();	 
        },
        'item.update.directus_files:after' => function ($item) 
        {
	        if ($item) {
		        $file = ArrayUtils::get($item, 'filename_disk');
		        
		        FileSystem::Thumbnailer([], false, $file);	        
	        }
	        else FileSystem::Thumbnailer();	  
        }
    ],
    'filters' => [
	    'item.update.directus_users:before' => function (Payload $payload) {
		    $home_page = Api::Settings('home_page', true);
		    
		    if ($home_page && $payload->get('last_page')) $payload->set('last_page', $home_page);
		    
		    return $payload;
	    }
    ]
];
