<?php

/*
	Author - PhilleepFlorence
	Description - Archive Directus Activity and Revisions Collections - Requires the Super Admin Token!
	Collections - directus_activity & directus_revisions 
	Endpoint - /app/custom/database/archive
	Authentication - yes (Admin Only)!
	Parameters
		date - only archive rows created before the date - defaults to NOW()
		folder - the directory in which the rows will be stored - defaults to .cache/<project>/database/archives/<date>
		super_admin_token - super admin token (prevents non admins from changing DB Schema)
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Database;

use Directus\Custom\Helpers\Database;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Archive
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Database::Archive($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
