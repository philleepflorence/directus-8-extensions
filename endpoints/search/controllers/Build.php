<?php

/*
	Author - PhilleepFlorence
	Description - Load contents from collections and compile into a single cache collection
	Collections - contents_search_cache, app_collections_configuration 
	Endpoint - /app/custom/search/cache
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
		collection - the name of the app_collections_configuration.collection to cache
*/

namespace Directus\Custom\Endpoints\Search;

use Directus\Custom\Helpers\Search;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Build
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    $collection = ArrayUtils::get($_REQUEST, 'collection');
	    
	    $result = Search::Build($collection, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
