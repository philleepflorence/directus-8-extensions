<?php

/*
	Author - PhilleepFlorence
	Description - Divider Extension Content Loader and Helper
	Endpoint - /app/custom/directus/divider
	Authentication - Access Token - Read Only!
*/

namespace Directus\Custom\Endpoints\Directus;

use Directus\Custom\Helpers\Directus;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Divider
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Directus::Divider($_REQUEST, $debug); 
	    	    
	    return $response->withJSON($result);  
    }
}
