<?php

/*
	Author - PhilleepFlorence
	Description - Update the name of a collection in the Database
	Collections - Directus Collections 
	Endpoint - /app/custom/database/collection
	Authentication - yes (Admin Only)!
	Parameters
		old_name - the current name of the collection
		new_name - the new name of the collection
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Database;

use Directus\Custom\Helpers\Database;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Collection
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Database::Collection($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
