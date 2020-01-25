<?php

/*
	Author - PhilleepFlorence
	Description - Authenticate User using username/email address and password and return login_token
	Collections - app_users
	Endpoint - /app/custom/auth/login
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

class Login
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Auth::Login($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
