<?php

/*
	Author - PhilleepFlorence
	Description - Back up all collections in MySQL or MariaDB as .SQL - Requires the Super Admin Token!
	Collections - All Collections 
	Endpoint - /app/custom/database/backup
	Authentication - yes (Admin Only)!
	Parameters
		file - the name of the file to save in the uploads directory (uploads/app/database/...)
		debug - return JSON data for debugging
*/

namespace Directus\Custom\Endpoints\Database;

use Directus\Custom\Helpers\Database;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Backup
{	
    public function __invoke (Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = Database::Backup($_REQUEST, $debug); 
	    
	    return $response->withJSON($result);  
    }
}
