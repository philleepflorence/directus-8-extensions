<?php

/*
	Author - PhilleepFlorence
	Description - File System Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Application\Application;
use Directus\Util\ArrayUtils;

use Intervention\Image\ImageManagerStatic as Image;

use function Directus\base_path;
use function Directus\get_api_project_from_request;
use function Directus\get_directus_files_settings;
use function Directus\get_directus_thumbnail_settings;

class FileSystem 
{
	protected static $config = [
		"options" => [
			"200x200" => [
				"action" => "crop",
				"quality" => "good"
			]
		],
		"extensions" => [
			"jpg", 
			"jpeg", 
			"png"
		],
		"sizes" => [
			"200x200", 
			"320x320", 
			"480x480", 
			"640x640", 
			"960x960", 
			"1280x1280", 
			"1600x1600", 
			"1900x1900"
		]
	];
	
	/*
		Returns the full path to the public storage (CDN) directory
		
		@return string
	*/
	
	public static function CDN ($path = '')
	{
		$app = Application::getInstance();
	    $container = $app->getContainer();
	    $project = get_api_project_from_request();       
	    $basepath = base_path();
	    
	    $directory = rtrim("{$basepath}/public/uploads/{$project}/{$path}", '/');
	    
	    return $directory;
	}	
	
	/*
		Load and process file contents
		
		@return string|array
	*/
	
	public static function Get ($filename = null, $json = null)
	{
		if (!is_file($filename)) return null;

        $contents = file_get_contents($filename);

        if (!$json) return $contents;

        try
        {
            $contents = json_decode($contents, true);
        }
        catch (Exception $e)
        {
            return null;
        }

        return $contents;
	}
	
	/*
		Set File Contents and create file if applicable
		
		@return boolean
	*/
	
	public static function Set ($filename = null, $data = null, $permission = 0777)
	{
		if (!$filename) return null;

        $folder = dirname($filename);

        if (!is_dir($folder)) @mkdir($folder, $permission, true);

        if (!is_dir($folder)) return null;

        $file = @fopen($filename, "w+");

        @fwrite($file, $data);

        @fclose($file);
        
        @chmod($filename, $permission);

        return true;
	}
	
	/*
		Process all the applicable sizes in the virtual CDN.
		ARGUMENTS:
			$params - @Array: Paramters
				extensions - @Array: Array of allowed extensions (SVG or GIF are not resizable currently)!
				qualities - @Array: Arrays of qualities to process
				files - @String CSV or @Array: List of file names to process (defaults to origninals)
			$debug - @Boolean: Return result without processing Thumbnails
			$currfile - @String - CSV: Process only the file(s) sent
		Thumbnailer Parameters:
			Originals - Path to original images
			Thumbnails - Path to Thumbnail directory
			Configuration - get_directus_thumbnail_settings(): Thumbnail Settings
			Filepath - Path to the Thumbnail to be created
			Params - Additional Parameters to pass to Thumbnailer
				Format - Extension to match against file for validation
				
		@return array
	*/
	
	public static function Thumbnailer ($params = [], $debug = false, $currfile = null) 
	{
		$params = $params ?: [];
		
		$app = Application::getInstance();
	    $container = $app->getContainer();
	    $project = get_api_project_from_request();
	    	    
        $filesettings = get_directus_files_settings();
        $thumbnailsettings = get_directus_thumbnail_settings();
        
        $file_naming = ArrayUtils::get($filesettings, 'file_naming');
        $whitelists = ArrayUtils::get($thumbnailsettings, 'asset_whitelist');      
        
        if (!$file_naming || !$whitelists) return [
			"error" => true,
			"message" => Api::Responses('filesystem.thumbnailer.validation')   
	    ];
	    
	    if (is_string($currfile)) $currfile = explode(',', $currfile);
	    
	    $storage = $app->getConfig()->get('storage');
	    $basepath = base_path();
	    $whitelists = json_decode($whitelists);
	    $whitelists = Utils::ToArray($whitelists);
	    
        $pattern = $file_naming === "file_id" ? '/^[0][0-9]{0,11}$/' : '/^[0-9a-fA-F]{8}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{4}\-[0-9a-fA-F]{12}$/';
        $originals = "{$basepath}/" . ArrayUtils::get($storage, 'root');
        $thumbnails = "{$basepath}/" . ArrayUtils::get($storage, 'thumb_root');
        
        # Loop through originals if no file sent or process single file
        
        $files = ArrayUtils::get($params, 'files');
        
        if (is_string($files)) $files = explode(',', $files);
        
        $files = is_array($files) ? $files : scandir($originals);
	    $extensions = ArrayUtils::get($params, 'extensions') ?: self::$config['extensions'];
	    $images = [];

	    try 
	    {
		    # Go through all the configuration rows of the Whitelist (from Directus Settings)
		    
		    foreach ($whitelists as $whitelist)
		    {
			    # Loop through and process all the files - where applicable
			    
			    foreach ($files as $index => $file) 
			    {
				    $extension = explode('.', $file);
				    $filename = reset($extension);
				    $extension = end($extension);
				    $extension = strtolower($extension);
				    
				    if (!in_array($extension, $extensions) || ($pattern && !preg_match($pattern, $filename)) || (is_array($currfile) && !in_array($file, $currfile))) 
				    {
					    unset($files[$index]);
					    
					    continue;
				    }
				    
				    $fit = ArrayUtils::get($whitelist, 'fit');
				    $quality = ArrayUtils::get($whitelist, 'quality');
				    $width = ArrayUtils::get($whitelist, 'width');
				    $height = ArrayUtils::get($whitelist, 'height');
				    $filepath = "{$thumbnails}/{$width}/{$height}/{$fit}/{$file}";
				    $original = "{$originals}/{$file}";
				    				    
				    if (file_exists($filepath)) continue;
				    				    					     
				    $thumbnail = Image::make($original);
				    
				    if ($fit === "crop")
				    {
					    $thumbnail->fit($width, $height, function ($constraint)
					    {
						    $constraint->upsize();
					    });
				    }
				    else
				    {
					    $thumbnail->resize($width, $height, function ($constraint)
					    {
						    $constraint->aspectRatio();
						    $constraint->upsize();
					    });
				    }
				    
				    $data = (string) $thumbnail->encode($extension, $quality);
				    
				    Filesystem::set($filepath, $data);
				    
				    $created = file_exists($filepath);
				    
				    if ($created) array_push($images, $filepath);
			    }			    
		    }
		    
		    $imageslen = count($images);
		    $fileslen = count($files);
		    
		    return [
			    "meta" => [
				    "mode" => "thumbnailer",
				    "sizes" => $whitelists,
				    "images" => $imageslen,
				    "files" => $fileslen
			    ],
			    "data" => $images
		    ];
	    }
	    catch (Exception $e)
	    {
		    return [
			    "error" => true,
				"message" => str_replace('{{message}}', $e->getMessage(), Api::Responses('filesystem.thumbnailer.exception'))
			    
		    ];
	    }
	}
	
	/*
		FileSystem Helper - Unlink all files and sub folders in a directory or remove file
		
		@return boolean
	*/
	
	public static function Unlink ($path = NULL)
	{
		if (is_dir($path))
		{
			$files = glob("{$path}/*");
			
			foreach ($files as $file)
			{
				if (is_file($file)) unlink($file);
				elseif (is_dir($file)) FileSystem::Unlink($file);
			}
			
			return @rmdir($path);
		}
		elseif (is_file($path)) return unlink($path);
	}
}