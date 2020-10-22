<?php

/*
	Author - PhilleepFlorence
	Description - Load all the applicable files in the uploads directory of Directus or the External CDN - Auxilary to the Directus Files Module
	Collections - app_configuration 
	Endpoint - /app/custom/cdn/files
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Campaigns;

use Directus\Custom\Helpers\Campaigns;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Send
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Campaigns::Send($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
