<?php

/*
	Author - PhilleepFlorence
	Description - Submit Application Form Data to the App Mailbox
	Collections - app_mailbox 
	Endpoint - /app/custom/form/submit
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Form;

use Directus\Custom\Helpers\Form;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Submit
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Form::Submit($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
