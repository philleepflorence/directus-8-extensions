<?php

/*
	Author - PhilleepFlorence
	Description - Add an application user to the Directus DB using email address and full name (and details)
	Collections - app_users, app_configuration, app_mailbox, and app_templates
	Endpoint - /app/custom/auth/register
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

class Register
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Auth::Register($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
