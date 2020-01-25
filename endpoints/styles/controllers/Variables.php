<?php

/*
	Author - PhilleepFlorence
	Description - Process Less Styles Variables
	Collections - app_colors 
	Endpoint - /app/custom/styles/variables
	Authentication - yes!
	Parameters
		debug - return JSON data for debugging
		path - relative file path to update or save Less Variables (must be in CDN or storage)
		mode - the type of variables less or css
*/

namespace Directus\Custom\Endpoints\Styles;

use Directus\Custom\Helpers\FileSystem;
use Directus\Custom\Helpers\Styles;

use Directus\Application\Http\Request;
use Directus\Application\Http\Response;
use Directus\Util\ArrayUtils;

use function Directus\base_path;

class Variables
{	
    public function __invoke(Request $request, Response $response)
    {
	    $debug = filter_var(( ArrayUtils::get($_REQUEST, 'debug') ), FILTER_VALIDATE_BOOLEAN);
	    $write = filter_var(( ArrayUtils::get($_REQUEST, 'write') ), FILTER_VALIDATE_BOOLEAN);
	    $mode = ArrayUtils::get($_REQUEST, 'mode');
	    
	    $styles = $mode === "less" ? Styles::LESS($write, $debug) : Styles::CSS($write, $debug);
	    
	    if ($debug) return $response->withJson($styles);
	    
	    $style = ArrayUtils::get($styles, 'style');
	    
	    header("Content-type: text/css; charset: UTF-8");
	    
	    print $style;
	    
	    die();	    
    }
}
