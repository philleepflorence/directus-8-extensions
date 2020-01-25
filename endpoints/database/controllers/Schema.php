<?php

/*
	Author - PhilleepFlorence
	Description - Export Collections Schema in MySQL or MariaDB as .SQL - Requires the Super Admin Token!
	Collections - All Collections 
	Endpoint - /app/custom/database/schema
	Authentication - yes (Admin Only)!
	Parameters
		collections - CSV list of collections to export
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Database;

use Directus\Custom\Helpers\Database;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Schema
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Database::Schema($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
