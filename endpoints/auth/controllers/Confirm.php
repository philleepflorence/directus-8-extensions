<?php

/*
	Author - PhilleepFlorence
	Description - Confirm user via token and authorize for authentication
	Collections - app_users, app_configuration, app_mailbox, and app_templates
	Endpoint - /app/custom/auth/confirm
	Authentication - yes!
	Parameters
		form - form object with reset_token
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Auth;

use Directus\Custom\Helpers\Auth;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Confirm
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Auth::Confirm($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
