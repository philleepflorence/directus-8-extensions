<?php

/*
	Author - PhilleepFlorence
	Description - Display Email HTML in browser
	Endpoint - /app/custom/mail/browser
	Authentication - no!
	Parameters
		UUID - The UUID Filename
*/

namespace Directus\Custom\Endpoints\Mail;

use Directus\Custom\Helpers\Mail;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Browser
{	
    public function __invoke (Request $request, Response $response)
    {
	    $result = Mail::Browser($_REQUEST); 
	    	    
	    return $response->withJSON($result);  
    }
}
