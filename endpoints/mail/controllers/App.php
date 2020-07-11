<?php

/*
	Author - PhilleepFlorence
	Description - Load Configuration for the Directus App External Script
	Endpoint - /app/custom/curl/content
	Authentication - no!
	Parameters
		user - The ID of the Directus API User
*/

namespace Directus\Custom\Endpoints\Directus;

use Directus\Custom\Helpers\Directus;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class App
{	
    public function __invoke (Request $request, Response $response)
    {
	    $result = Directus::App($_REQUEST); 
	    	    
	    return $response->withJSON($result);  
    }
}
