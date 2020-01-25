<?php

/*
	Author - PhilleepFlorence
	Description - Update the profle or account settings for an authenticated user
	Collections - app_users, app_configuration, app_mailbox, and app_templates
	Endpoint - /app/custom/user/settings
	Authentication - yes!
	Parameters
		form - form object with user ID and Profile Data
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\User;

use Directus\Custom\Helpers\User;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Settings
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = User::Settings($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
