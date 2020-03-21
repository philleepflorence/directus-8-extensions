<?php

/*
	Author - PhilleepFlorence
	Description - Get Analytics of the Application Visitors
	Collections - app_browsers, contents_analytics, contents_analytics_locations
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

class Application
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Analytics::Application($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
