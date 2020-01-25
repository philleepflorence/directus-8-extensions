<?php

/*
	Author - PhilleepFlorence
	Description - Process the Notifications Relation Collection (useful for new users)
	Collections - app_users, app_notifications, joins_app_users_notifications, app_configuration, app_mailbox, and app_templates
	Endpoint - /app/custom/user/notifications
	Authentication - yes!
	Parameters
		form - form object with a filter for the users collection
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\User;

use Directus\Custom\Helpers\User;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;

use Directus\Util\ArrayUtils;

class Notifications
{
    public function __invoke(Request $request, Response $response)
    {
        $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = User::Notifications($_REQUEST, $debug);    
	    
	    return $response->withJson($result);
    }
}
