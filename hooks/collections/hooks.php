<?php

use Directus\Custom\Helpers\Api;
use Directus\Custom\Helpers\Debugger;
use Directus\Custom\Helpers\FileSystem;
use Directus\Custom\Helpers\User;
use Directus\Custom\Helpers\Utils;

use Directus\Hook\Payload;
use Directus\Util\ArrayUtils;

require_once dirname(dirname(__DIR__)) . '/helpers/functions.php';

return [
    'actions' => [
        'file.save:after' => function ($file) 
        {
	        if (stripos($_SERVER['REQUEST_URI'], '/app/files') !== false) FileSystem::Thumbnailer();	
        },
        'item.create.app_users:after' => function ($item) 
        {
	        if ($item) {
		        $id = ArrayUtils::get($item, 'id');
		        
		        User::Notifications([$id]);	        
	        }
	        else User::Notifications();	  
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
	    'item.create:before' => function (Payload $payload) 
	    {
		    $collection = $payload->attribute('collection_name');
		    
		    if (stripos($collection, 'directus_') === 0) return $payload;
		    
		    $title = $payload->get('title');
		    $name = $payload->get('name');
		    
		    if ($title) $payload->set('title', Utils::TitleCase($title));
		    
		    if ($name) $payload->set('name', Utils::TitleCase($name));
		    
		    return $payload;
	    },
	    'item.update:before' => function (Payload $payload) 
	    {
		    $collection = $payload->attribute('collection_name');
		    
		    if (stripos($collection, 'directus_') === 0) return $payload;
		    
		    $title = $payload->get('title');
		    $name = $payload->get('name');
		    
		    if ($title) $payload->set('title', Utils::TitleCase($title));
		    
		    if ($name) $payload->set('name', Utils::TitleCase($name));
		    
		    return $payload;
	    },
	    'item.update.directus_users:before' => function (Payload $payload) 
	    {
		    $home_page = Api::Settings('home_page', true);
		    
		    if ($home_page && $payload->get('last_page')) $payload->set('last_page', $home_page);
		    
		    return $payload;
	    }
    ]
];
