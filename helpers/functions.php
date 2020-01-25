<?php 

/*
	Author - PhilleepFlorence
	Description - Global Helper Functions
*/
	
/*
	AutoLoad - Helper Files - Should Run at application.boot!
*/

$helpers = scandir(__DIR__);
	
foreach( $helpers as $file ) 
{
    if ('.' === $file || '..' === $file || __FILE__ === $file) continue;
    
    include_once($file);
}

/*
	function array_filter_recursive
	Exactly the same as array_filter except this function filters within multi-dimensional arrays
	
	@param array
	@param string optional callback function name
	@param bool optional flag removal of empty arrays after filtering
	@return array merged array
*/

if (!function_exists('array_filter_recursive'))
{
	function array_filter_recursive($array, $callback = null, $remove_empty_arrays = false) 
	{
		foreach ($array as $key => & $value) 
		{ 
			if (is_array($value)) 
			{
				$value = array_filter_recursive($value, $callback);
				
				if ($remove_empty_arrays && !(bool) $value && !is_numeric($value)) 
				{
					unset($array[$key]);
				}
			}
			else 
			{
				if (!is_null($callback) && !$callback($value)) 
				{
					unset($array[$key]);
				}
				elseif (!(bool) $value && !is_numeric($value)) 
				{
					unset($array[$key]);
				}
			}
		}
		
		unset($value);
		
		return $array;
	}
}


if (!function_exists('getallheaders')) 
{
    function getallheaders() 
    {
	    $headers = [];
	    
	    foreach ($_SERVER as $name => $value) 
	    {
	        if (substr($name, 0, 5) == 'HTTP_') {
	            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
	        }
	    }
	    
	    return $headers;
    }
}

if (!function_exists('format_bytes'))
{
	function format_bytes($bytes = 0)
	{
		if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, 2) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, 2) . ' KB';
        }
        elseif ($bytes > 1)
        {
            $bytes = $bytes . ' bytes';
        }
        elseif ($bytes == 1)
        {
            $bytes = $bytes . ' byte';
        }
        else
        {
            $bytes = '0 bytes';
        }

        return $bytes;
	}
}

if (!function_exists('rmdir_recursive'))
{
	function rmdir_recursive($target) 
	{
		if(is_dir($target))
	    {
	        $files = scandir($target);
	
	        foreach( $files as $file ) 
	        {
		        if ('.' === $file || '..' === $file) continue;
		        
		        $filename = "{$target}/{$file}";
		        
		        if (is_dir($filename)) rmdir_recursive($filename);
		        else unlink($filename);
	        }
		        
		    rmdir($target);
	    } 
	    elseif(is_file($target)) 
	    {
	        unlink( $target );  
	    }
	}
}

if (!function_exists('chmod_recursive'))
{
	function chmod_recursive($path, $permission) 
	{
		if (is_file($path))
		{
			chmod($path, $permission);
		}
		else
		{
			$dir = new DirectoryIterator($path);
			
			foreach ($dir as $item)
			{
				chmod($item->getPathname(), $permission);
				
				if ($item->isDir() && !$item->isDot()) chmod_recursive($item->getPathname(), $permission);
			}			
		}	    
	}
}	