<?php

use Directus\Custom\Helpers\Debug;
use Directus\Custom\Helpers\Response;

use Directus\Hook\Payload;
use Directus\Util\ArrayUtils;

return [
    'filters' => [
        'response.GET' => function (Payload $payload) 
        {
	        $data = $payload->getData();
	        $collection = $payload->attribute('collection');
	        
	        $editable = ArrayUtils::get($_REQUEST, 'contenteditable');
	        $method = ArrayUtils::get($_SERVER, 'REQUEST_METHOD');
			$url = ArrayUtils::get($_SERVER, 'REQUEST_URI');
			
			# Process Files
			
			$data = Response::CDN($data);
			
			# Editable Links
			
			if ($collection && $editable) $data = Response::Editable($data, $collection);
			
			$payload->replace($data);

			return $payload;
        }
    ]
];
