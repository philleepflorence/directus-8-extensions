<?php

/*
	Author - PhilleepFlorence
	Description - Search through all applicable collections for a query - String fields only
	Collections - directus_fields 
	Endpoint - /app/custom/search/database
	Authentication - yes!
	Parameters ($params):
		collections - @String: List of collections in the DB to search
		query - @String: the query string to find in collection items
*/

namespace Directus\Custom\Endpoints\Search;

use Directus\Custom\Helpers\Search;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Database
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	     
	    $result = Search::Database($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
