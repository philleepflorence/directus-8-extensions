<?php

/*
	Author - PhilleepFlorence
	Description - Directus Cache Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

use function Directus\base_path;
use function Directus\get_api_project_from_request;

class Cache 
{
	private static function Directory ()
	{
		$project = get_api_project_from_request();       
	    $basepath = base_path();
	    $directory = "{$basepath}/.cache/cache";
	    
	    return $directory;
	}
	
	/*
		Clear the Cached Items in the Cache directory - Sessions. 
		Auxilary to the Directus Cache Module.
	*/
		
	public static function Session ()
	{
	    $directory = Cache::Directory();
	    
	    if (is_dir($directory)) Filesystem::Unlink($directory);
	    	    
	    return !is_dir($directory);
	}
}