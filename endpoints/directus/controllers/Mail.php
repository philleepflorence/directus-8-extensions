<?php

/*
	Author - PhilleepFlorence
	Description - Post Metadata to Directus Users
	Endpoint - /app/custom/directus/mail
	Authentication - Admin Token!
*/

namespace Directus\Custom\Endpoints\Directus;

use Directus\Custom\Helpers\Directus;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Mail
{	
    public function __invoke (Request $request, Response $response)
    {
	    $result = Directus::Mail($_REQUEST); 
	    	    
	    return $response->withJSON($result);  
    }
}
