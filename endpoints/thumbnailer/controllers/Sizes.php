<?php

namespace Directus\Custom\Endpoints\Thumbnailer;

use Directus\Custom\Helpers\FileSystem;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

class Sizes
{	
    public function __invoke(Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    $extensions = ArrayUtils::get($_REQUEST, 'extensions');
	    $quality = ArrayUtils::get($_REQUEST, 'quality');
	    $files = ArrayUtils::get($_REQUEST, 'files');
	    
	    $result = FileSystem::Thumbnailer([
		    "extensions" => $extensions,
		    "qualities" => $quality,
		    "files" => $files
	    ], $debug);
	    
	    return $response->withJson($result);	    
    }
}
