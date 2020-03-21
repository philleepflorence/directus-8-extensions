<?php

/*
	Author - PhilleepFlorence
	Description - Get Analytics information for the Directus Custom Modules
	Collections - app_icons
	Endpoint - /app/custom/analytics/modules
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Analytics;

use Directus\Custom\Helpers\Analytics;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Modules
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Analytics::Modules($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
