<?php

/*
	Author - PhilleepFlorence
	Description - Post Metadata to Directus Users
	Endpoint - /app/custom/curl/content
	Authentication - no!
	Parameters
		user - ID of the user
		metadata - section, key, value, created
*/

namespace Directus\Custom\Endpoints\Directus;

use Directus\Custom\Helpers\Directus;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Metadata
{	
    public function __invoke (Request $request, Response $response)
    {
	    $result = Directus::Metadata($_REQUEST); 
	    	    
	    return $response->withJSON($result);  
    }
}
