<?php

/*
	Author - PhilleepFlorence
	Description - Locate user via email for updating credentials
	Collections - app_users, app_configuration, app_mailbox, and app_templates
	Endpoint - /app/custom/auth/credentials
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

class Credentials
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Auth::Credentials($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
