<?php

/*
	Author - PhilleepFlorence
	Description - Delete authentication cookies/session and clear out login_token
	Collections - app_users
	Endpoint - /app/custom/auth/logout
	Authentication - yes!
	Parameters
		user - user object
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Auth;

use Directus\Custom\Helpers\Auth;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Logout
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Auth::Logout($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
