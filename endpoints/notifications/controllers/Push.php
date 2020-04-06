<?php

/*
	Author - PhilleepFlorence
	Description - Push notifications via SMS, Push, or Email to Application Users
	Collections - app_users 
	Endpoint - /app/custom/notifications/push
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Notifications;

use Directus\Custom\Helpers\Notifications;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Push
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Notifications::Push($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
