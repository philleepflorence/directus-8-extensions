<?php

/*
	Author - PhilleepFlorence
	Description - Search cache collection for a query
	Collections - contents_search_cache 
	Endpoint - /app/custom/search/cache
	Authentication - yes!
	Parameters ($params):
		debug - return JSON data for debugging
		query - query string to find in collection items
		mode - the mode of search: strict (match phrase), all (match all words), any (match any word)
		privilege - the privilege of the application user (must be >= row.privilege)
		status - the status type (draft, published, soft_delete)
		collection - filter the items by collection (search all rows by default)
*/

namespace Directus\Custom\Endpoints\Search;

use Directus\Custom\Helpers\Search;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Cache
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	     
	    $result = Search::Cache($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
