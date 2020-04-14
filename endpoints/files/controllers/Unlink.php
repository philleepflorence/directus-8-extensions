<?php

namespace Directus\Custom\Endpoints\Files;

use Directus\Custom\Helpers\FileSystem;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Unlink
{	
    public function __invoke(Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    
	    $result = FileSystem::Files($request, $debug);
	    
	    return $response->withJson($result);	    
    }
}
