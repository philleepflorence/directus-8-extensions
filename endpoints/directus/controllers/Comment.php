<?php

/*
	Author - PhilleepFlorence
	Description - Comment Email Notifications
	Endpoint - /app/custom/directus/comment
	Authentication - Access Token - Read Only!
*/

namespace Directus\Custom\Endpoints\Directus;

use Directus\Custom\Helpers\Directus;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Comment
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Directus::Comment($_REQUEST, $debug); 
	    	    
	    return $response->withJSON($result);  
    }
}
