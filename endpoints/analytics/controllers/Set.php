<?php

/*
	Author - PhilleepFlorence
	Description - Add Analytics and Browser data to the Database
	Collections - app_browsers, contents_analytics
	Endpoint - /app/custom/analytics/set
	Authentication - yes!
	Parameters
		analytics - analytics object (see contents_analytics schema for more)
		browser - browser object (see app_browsers schema for more)
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Analytics;

use Directus\Custom\Helpers\Analytics;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Set
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Analytics::Set($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
