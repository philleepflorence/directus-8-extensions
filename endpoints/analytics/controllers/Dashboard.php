<?php

/*
	Author - PhilleepFlorence
	Description - Get Analytics information for the Directus Dashboard Module
	Collections - directus_files, directus_collections, directus_users
	Endpoint - /app/custom/analytics/dashboard
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Analytics;

use Directus\Custom\Helpers\Analytics;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Dashboard
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Analytics::Dashboard($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
