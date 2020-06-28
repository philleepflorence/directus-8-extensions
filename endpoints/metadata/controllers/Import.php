<?php

/*
	Author - PhilleepFlorence
	Description - Import the HTML Metadata from an external URL
	Collections - N/A 
	Endpoint - /app/custom/metadata/import
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Metadata;

use Directus\Custom\Helpers\Curl;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Import
{	
    public function __invoke (Request $request, Response $response)
    {
	    $url = ArrayUtils::get($_REQUEST, 'url');
	    $options = ArrayUtils::get($_REQUEST, 'options');
	    $params = ArrayUtils::get($_REQUEST, 'params');
	    
	    $encode = filter_var(( ArrayUtils::get($_REQUEST, 'encode') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Curl::Metadata($url, $options, $encode, $params); 
	    
	    return $response->withJSON($result);  
    }
}
