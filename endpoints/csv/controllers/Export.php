<?php

/*
	Author - PhilleepFlorence
	Description - Export DB Collection as CSV
	Collections - N/A (Access required for collections to Export)
	Endpoint - /app/custom/csv/export
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\CSV;

use Directus\Custom\Helpers\CSV;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Export
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = CSV::Export($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
