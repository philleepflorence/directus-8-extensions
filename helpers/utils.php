<?php

/*
	Author - PhilleepFlorence
	Description - General Utility Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateUtils;
use Directus\Util\StringUtils;

class Utils 
{
	private static $MimeTypes = [
		'txt' => 'text/plain',
        'htm' => 'text/html',
        'html' => 'text/html',
        'php' => 'text/html',
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'swf' => 'application/x-shockwave-flash',
        'flv' => 'video/x-flv',

        # images
        'png' => 'image/png',
        'jpe' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'jpg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'ico' => 'image/vnd.microsoft.icon',
        'tiff' => 'image/tiff',
        'tif' => 'image/tiff',
        'svg' => 'image/svg+xml',
        'svgz' => 'image/svg+xml',

        # archives
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        'exe' => 'application/x-msdownload',
        'msi' => 'application/x-msdownload',
        'cab' => 'application/vnd.ms-cab-compressed',

        # audio/video
        'mp3' => 'audio/mpeg',
        'qt' => 'video/quicktime',
        'mov' => 'video/quicktime',

        # adobe
        'pdf' => 'application/pdf',
        'psd' => 'image/vnd.adobe.photoshop',
        'ai' => 'application/postscript',
        'eps' => 'application/postscript',
        'ps' => 'application/postscript',

        # ms office
        'doc' => 'application/msword',
        'rtf' => 'application/rtf',
        'xls' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',

        # open office
        'odt' => 'application/vnd.oasis.opendocument.text',
        'ods' => 'application/vnd.oasis.opendocument.spreadsheet'
	];
		
	/*
		Convert Image Stream to Data URL
		ARGUMENTS:
			$image = Image File Path
	*/
	
	public static function Base64 ($image = NULL)
	{
		if (!$image || stripos($image, ';base64,')) return $image;
		
		# If image is not internal, force external validation
		
		$parsed = parse_url($image);
		
		if (ArrayUtils::get($parsed, 'host') && !filter_var($image, FILTER_VALIDATE_URL)) return NULL;
		
		# Remove all URL variables to get extension - default to 'application/octet-stream'
		
		$filename = explode('?', $image);
		$filename = strtolower(array_shift($filename));
		
		$pathinfo = pathinfo($filename);
		$extension = ArrayUtils::get($pathinfo, 'extension');
		$basename = ArrayUtils::get($pathinfo, 'basename');
		
		# Read image path, convert to base64 encoding
		
		try
		{
			$imageData = file_get_contents($image);
			
			$imageData = base64_encode($imageData);
			
			# Format the image SRC:  data:{mime};base64,{data};			
			
			$src = 'data:' . ArrayUtils::get(SELF::$MimeTypes, $extension) .';base64,' . $imageData;
	
			return [
				"data" => $src,
				"base64" => $imageData,
				"extension" => $extension,
				"filename" => $basename,
				"src" => $image,
				"size" => getimagesize($src)
			];
		}
		
		# If getting image data fails, return NULL
		
		catch (Exception $e)
		{
			return NULL;
		}
	}
	
	/*
		Hash Utility Wrapper
		ARGUMENTS:
			$algorithm - @Boolean: True => sha256 or False => adler32
			$string - @String: String to pass through Hash method
			$salt - @String: Salt String to use with Hash - Defaults to current time
	*/
	
	public static function Hash ($algorithm = true, $string = '', $salt = NULL)
	{
		$salt = $salt ?: time();
		
		$algorithm = $algorithm ? 'sha256' : 'adler32';
		
		return hash($algorithm, "{$string}.{$salt}");
	}
	
	/*
		Parse Response Header
		ARGUMENTS:
			$header - @String: Incoming Header String
	*/
	
	public static function Headers ($header = '')
	{
		$details = split(':', $header, 2);
		$response = [];
		
		if (count($details) == 2)
		{
			$key   = trim($details[0]);
            $value = trim($details[1]);

            $response[$key] = $value;
		}
		
		return $response;
	}
	
	/*
		RegExp Validation Wrapper - Check to see if argument is a RegEx Pattern
		ARGUMENTS: 
			$pattern - @String: Regular Expression Pattern
	*/
	
	public static function RegExp ($pattern = '')
	{
		return @preg_match($pattern, NULL) !== FALSE;
	}
	
	/*
		Convert Standard Objects to Array
	*/
	
	public static function ToArray ($object = null)
	{
		if (is_object($object) || is_array($object)) return json_decode(json_encode($object), true);
		
		return $object;
	}
}