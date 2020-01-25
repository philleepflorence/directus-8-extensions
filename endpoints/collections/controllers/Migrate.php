<?php

/*
	Author - PhilleepFlorence
	Description - Normalize a JSON Input into SQL for migrations into a collection
	Endpoint - /app/custom/collections/migrate
	Authentication - yes!
	Parameters
		collection - the collection to create or migrate
		fields - Array of objects
			input - field to create
			output - field to create
		json - List of properties to json_decode (incoming JSON Strings)
		data - JSON Data Object
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Collections;

use Directus\Custom\Helpers\Collections;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Migrate
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Collections::Migrate($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
