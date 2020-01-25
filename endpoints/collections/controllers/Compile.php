<?php

/*
	Author - PhilleepFlorence
	Description - Similar to GraphQL, the endpoint allows for the configuration of multiple collections in a single response
	Collections - Any Directus Collections and app_collections_configuration
	Endpoint - /app/custom/collections/compile
	Authentication - yes!
	Parameters
		collections - collections from app_collections_configuration
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Collections;

use Directus\Custom\Helpers\Collections;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Compile
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Collections::Compile($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
