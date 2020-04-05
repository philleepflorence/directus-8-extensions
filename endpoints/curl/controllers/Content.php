<?php

/*
	Author - PhilleepFlorence
	Description - Similar to GraphQL, the endpoint allows for the configuration of multiple collections in a single response
	Endpoint - /app/custom/curl/content
	Authentication - no!
	Parameters
		url - URL of content to load
*/

namespace Directus\Custom\Endpoints\Curl;

use Directus\Custom\Helpers\Curl;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Content
{	
    public function __invoke (Request $request, Response $response)
    {
	    $url = ArrayUtils::get($_REQUEST, 'url');
	    
	    $started = round(microtime(true) * 1000);
	    
	    $result = Curl::Load($url); 
	    
	    $loaded = round(microtime(true) * 1000);
	    
	    return $response->withJSON([
		    "loaded" => ($loaded - $started),
		    "url" => $url,
		    "content" => $result
	    ]);  
    }
}
