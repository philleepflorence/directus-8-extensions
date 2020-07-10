<?php

/*
	Author - PhilleepFlorence
	Description - File System Helper Functions
*/

namespace Directus\Custom\Helpers;

use \ZipArchive;
	
use Directus\Application\Application;
use Directus\Util\ArrayUtils;

use Intervention\Image\ImageManagerStatic as Image;

use function Directus\base_path;
use function Directus\get_api_project_from_request;
use function Directus\get_directus_files_settings;
use function Directus\get_directus_thumbnail_settings;
use function Directus\generate_uuid4;
use function Directus\get_random_string;

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
	
	public static function Directory ($filename = NULL, $permission = 0777)
	{
		if (!$filename) return null;

        $folder = dirname($filename);

        if (!is_dir($folder)) @mkdir($folder, $permission, true);
        
        return is_dir($folder);
	}	
	
	/*
		Download Files as Zip Archive!
		
		@return array
	*/
	
	public static function Download ($files, $zipname = "download.zip")
	{
		$directory = FileSystem::Directory($zipname);   
		$filename = pathinfo($zipname, PATHINFO_BASENAME); 
        
		$zip = new ZipArchive;
		$zip->open($zipname, ZipArchive::CREATE | ZipArchive::OVERWRITE);
		
		foreach ($files as $file) $zip->addFile($file, pathinfo($file, PATHINFO_BASENAME));
		
		if (!is_file($zipname)) return false;
		
		$zip->close();
		
        header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");		
		header("Cache-Control: public");
        header("Content-Type: application/zip");
        header("Content-Transfer-Encoding: Binary");
        header("Content-Length: " . filesize($zipname));
        header("Content-Disposition: attachment; filename={$filename}");
        
        readfile($zipname);
        
        die();
	}
	
	/*
		Remove all images not included in the Directus Files Collection
		
		@return array
	*/
	
	public static function Files ($params = [], $debug = false) 
	{
		$basepath = base_path();
		$app = Application::getInstance();	    
	    $storage = $app->getConfig()->get('storage');
	    $originals = "{$basepath}/" . ArrayUtils::get($storage, 'root');
	    
		$folder = ArrayUtils::get($params, 'folder', $originals);
		
		# Get all the files in the folder
		
		$files = is_dir($folder) ? scandir($folder) : [];
		$response = [
			"meta" => [
				"mode" => "Files Unlink",
				"total" => 0,
				"unlink" => 0
			],
			"data" => []
		];
		
		$tableGateway = Api::TableGateway('directus_files');
		
		foreach ($files as $file)
		{
			if (strpos($file, '.') === 0) continue;
			
			$response['meta']['total']++;
			
			$parameters = [
				"limit" => 1,
				"fields" => "id,filename_disk",
				"filter" => [
					"filename_disk" => $file
				]
			];
			
			$entries = $tableGateway->getItems($parameters);
			$entries = ArrayUtils::get($entries, 'data.0');
			
			if (!$entries)
			{
				$realpath = "{$folder}/{$file}";				
				array_push($response['data'], [
					"file" => $file,
					"path" => $realpath,
					"exists" => is_file($realpath)
				]);
				$response['meta']['unlink']++;
				
				if (!$debug) FileSystem::Unlink($realpath);
			}
		}
	    
	    return $response;
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
		set_time_limit(3600);
		ini_set('memory_limit', '1024M');
		
		$params = $params ?: [];
		
		$app = Application::getInstance();
	    $container = $app->getContainer();
	    $project = get_api_project_from_request();
	    	    
        $filesettings = get_directus_files_settings();
        $thumbnailsettings = get_directus_thumbnail_settings();
        
        foreach($thumbnailsettings as &$thumbnailsetting) if (is_string($thumbnailsetting)) $thumbnailsetting = json_decode($thumbnailsetting);
        
        $file_naming = ArrayUtils::get($filesettings, 'file_naming');
        $whitelists = ArrayUtils::get($thumbnailsettings, 'asset_whitelist');     
        
        if (!$file_naming || !$whitelists) return [
			"error" => true,
			"message" => Api::Responses('filesystem.thumbnailer.validation')   
	    ];
	    
	    if (is_string($currfile)) $currfile = explode(',', $currfile);
	    
	    $storage = $app->getConfig()->get('storage');
	    $basepath = base_path();
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
				    
				    if ($debug === true) 
				    {
					    array_push($images, $filepath);
					    
					    continue;
				    }
				    				    					     
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
				    "debug" => $debug,
				    "file_name" => $file_naming,
				    "sizes" => $whitelists,
				    "processed_files" => $imageslen,
				    "loaded_files" => $fileslen,
				    "memory_usage" => memory_get_usage(),
					"memory_limit" => ini_get('memory_limit'),
				    "time_limit" => ini_get('max_execution_time')
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
		Migrate files from legacy Directus versions
	*/
	
	public static function Migrate ($params = [], $debug = false) 
	{
		# Get files that need to be updated
		
		set_time_limit(0);

		$tableGateway = Api::TableGateway('directus_files', true);
		$items = $tableGateway->getItems([
			"fields" => "id,filename_disk,filename_download,private_hash,checksum,title",
			"filter" => [
				"private_hash" => [
					"null" => 1
				]
			]
		]);

		$files = ArrayUtils::get($items, 'data');

		$response = [
			"meta" => [
				"mode" => "migrate",
				"total" => count($files)
			],
			"data" => $files
		];

		if (!count($files)) return $files;

		# Get File Originals Folder

		$basepath = base_path();
		$app = Application::getInstance();
		$storage = $app->getConfig()->get('storage');
		$originals = "{$basepath}/" . ArrayUtils::get($storage, 'root');   

		forEach ($files as &$file) {

			$realpath = "{$originals}/" . $file['filename_disk'];

			if (!is_file($realpath)) continue;

			$pathinfo = pathinfo($realpath);

			$title = ArrayUtils::get($file, 'title');

			$filename = generate_uuid4() . '.' . $pathinfo['extension'];

			$filename_disk = preg_replace("/[^a-z0-9.-]/", '-', strtolower($title));

			$filename_disk = preg_replace("/-{2,}/", '-', $filename_disk);

			$imageData = file_get_contents($realpath);			

			$imageData = base64_encode($imageData);

			ArrayUtils::set($file, 'realpath', $realpath);

			# Checksum - hash_file('md5', <file-data>)			

			# Private Hash - get_random_string()			

			# File Name - UUID: generate_uuid4()

			$id = ArrayUtils::get($file, 'id');

			$update = [

			    "filename_disk" => $filename,

			    "filename_download" => $filename_disk . '.' . $pathinfo['extension'],

			    "private_hash" => get_random_string(),

			    "checksum" => hash_file('md5', $realpath)

			];

			ArrayUtils::set($file, 'update', $update);

			if ($debug === true) continue;

			# Rename file

			$filepath = "{$originals}/" . $update['filename_disk'];

			rename($realpath, $filepath);

			# Update the Files Collection

			$tableGateway->updateRecord($id, $update);

	    }

		ArrayUtils::set($response, 'data', $files);

		return $response;

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