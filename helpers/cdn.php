<?php

/*
	Author - PhilleepFlorence
	Description - CDN Helper Methods (Uploads Directory or External CDN)
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

use function Directus\base_path;
use function Directus\get_api_project_from_request;

class CDN 
{
	private static $units = ['B', 'KB', 'MB', 'GB', 'TB'];
	
	/*
		Load all the applicable files in the uploads directory of Directus or the External CDN 
		Auxilary to the Directus Files Module
	*/
		
	public static function Files ($params = [], $debug)
	{
		$project = get_api_project_from_request();       
	    $basepath = base_path();
	    $directory = ArrayUtils::get($params, 'directory');
	    
	    $folder = rtrim("{$basepath}/public/uploads/{$project}/{$directory}", '/');
	    
	    $directories = scandir($folder);
	    $folders = [];
	    $meta = [
		    "files" => 0,
		    "directories" => 0,
		    "types" => []
	    ];
	    
	    foreach ($directories as $key => $directory)
	    {
		    if (strpos($directory, ".") !== 0) 
		    {
			    $directory_path = rtrim("{$folder}/{$directory}", '/');
			    $files = CDN::Scan($directory_path);
			    
			    $meta["directories"]++;
			    
			    array_walk($files, function (&$file) use ($folder, &$meta)
			    {
				    $currdirectory = str_replace($folder, "", ArrayUtils::get($file, "path"));
				    $currdirectory = ltrim($currdirectory, '/');
				    
				    if (ArrayUtils::get($file, "type") === "dir") $meta["directories"]++;
				    else $meta["files"]++;
				    
				    $extension = ArrayUtils::get($file, "extension");
				    
				    if ($extension)
				    {
					    $meta["types"][$extension] = $meta["types"][$extension] ?: 0;
					    
					    $meta["types"][$extension]++;
				    } 
				    
				    ArrayUtils::set($file, "directory", dirname($currdirectory));
			    });
			    
			    array_push($folders, [
				    "name" => $directory,
				    "path" => $directory_path,
				    "files" => $files
			    ]);
		    }
	    }
	    
	    return [
		    "meta" => $meta,
		    "data" => $folders
	    ];
	}
	
	private static function Bytes ($path)
	{
		$bytestotal = 0;
	    $path = realpath($path);
	    
	    if($path !== false && $path != '' && file_exists($path))
	    {
	        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)) as $object)
	        {
	            $bytestotal += $object->getSize();
	        }
	    }
	    
	    return CDN::Size($bytestotal);
	}
	
	private static function Number ($directory)
	{
		$fi = new \FilesystemIterator($directory, \FilesystemIterator::SKIP_DOTS);
		
		return iterator_count($fi);
	}
	
	private static function Size ($bytes, $precision = 2)
	{
	    $bytes = max($bytes, 0); 
	    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
	    $pow = min($pow, count(CDN::$units) - 1); 
	    $bytes /= (1 << (10 * $pow)); 
		
		return [
			"value" => round($bytes, $precision),
			"unit" => CDN::$units[$pow]
		];
	}
	
	private static function Title ($title)
	{
		return ucwords( str_replace( ["-", "_"], " ", $title) );
	}
	
	private static function Scan ($path, $response = [])
	{
		$files = dir($path);
		
		while (FALSE !== ($entry = $files->read()))
		{
			if($entry{0} == ".") continue;
			
			$real_path = "{$path}/{$entry}";
			
			if(is_dir($real_path))
			{
				array_push($response, [
					"name" => CDN::Title($entry),
					"path" => $real_path,
					"type" => filetype($real_path),
					"modified" => filemtime($real_path),
					"size" => [
						"value" => CDN::Number($real_path),
						"unit" => "Files"
					]
				]);
				
				$response = array_merge($response, CDN::Scan($real_path));
			}
			elseif (is_file($real_path))
			{
				array_push($response, [
					"name" => $entry,
					"path" => $real_path,
					"type" => filetype($real_path),
					"modified" => filemtime($real_path),
					"size" => CDN::Size(filesize($real_path)),
					"mime" => mime_content_type($real_path),
					"extension" => pathinfo($real_path, PATHINFO_EXTENSION)
				]);
			}
		}
		
		$files->close();
		
		return $response;
	}
}