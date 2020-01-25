<?php

/*
	Author - PhilleepFlorence
	Description - Send form data from an aoplication to one or more users
	Collections - app_users 
	Endpoint - /app/custom/form/mail
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Form;

use Directus\Custom\Helpers\Form;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Mail
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Form::Mail($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
