<?php

/*
	Author - PhilleepFlorence
	Description - Update metadata for an authenticated user
	Collections - app_users, app_users_metadata, app_configuration, app_mailbox, and app_templates
	Endpoint - /app/custom/user/metadata
	Authentication - yes!
	Parameters
		form - form object with user ID and metadata
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\User;

use Directus\Custom\Helpers\User;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Metadata
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = User::Metadata($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
