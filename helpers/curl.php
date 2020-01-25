<?php

/*
	Author - PhilleepFlorence
	Description - cURL Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateUtils;
use Directus\Util\StringUtils;

use \DOMDocument;

class Curl 
{
	protected static $load = [
		"useragent" => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36'
	];	
	protected static $metadata = [
		"options" => [
			'title' => 'title', 
			'description' => 'description', 
			'image' => 'image', 
			'author' => 'author', 
			'sitename' => 'sitename', 
			'share:image' => 'image', 
			'og:image' => 'image',
			'twitter:image:src' => 'image'
		]
	];
	
	/*
		Multi purpose CURL method for connecting to external CRM APIs
		ARGUMENTS:
			$api = [
				@url - endpoint,
				@method - POST, PUT, GET, DELETE et al - DEFAULTS TO POST
				@authentication - boolean: authenticate connection
				@username - authentication username
				@password - authentication password
			]
	*/
	
	public static function Api ($api = [], $post = [])
	{
		$ch = curl_init(ArrayUtils::get($api, 'url'));
		$headers = ArrayUtils::get($api, 'headers') ?: ["Content-Type: application/json"];
		$headers = is_string($headers) ? explode("\n", $headers) : $headers;
		$method = ArrayUtils::get($api, 'method') ?: 'GET';
		$query = ArrayUtils::get($api, 'format') === 'query' ? http_build_query($post) : json_encode($post);
		$header = ArrayUtils::get($api, 'response') === 'json' ? false : true;
    
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	    curl_setopt($ch, CURLOPT_HEADER, $header);
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
	    
	    if (ArrayUtils::get($api, 'authentication') && ArrayUtils::get($api, 'username')) 
	    {
		    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		    curl_setopt($ch, CURLOPT_USERPWD, ArrayUtils::get($api, 'username') . ":" . ArrayUtils::get($api, 'password'));
	    }
	    
	    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	    
	    if (count($post)) curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
	    		    
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	    
	    $response = curl_exec($ch);
	    
	    if ($response === false) return [
		    'error' => curl_error($ch)
	    ];
	    
	    $info = curl_getinfo($ch) ?: [];
	    $response = $header ? $response : json_decode($response, true);
	    
	    $return = [
		    'code' => $info['http_code'],
		    'duration' => $info['total_time'],
		    'error' => curl_error($ch),
		    'size' => $info['size_download'],
		    'url' => $info['url'],
		    'response' => $response
	    ];
	    
	    curl_close($ch);
	    
	    return $return;
	}
	
	/*
		General Purpose cURL Method for loading content
		ARGUMENTS:
			$url - @String: Valid URL String
			$POST - @Array: Array for POSTing - Optional if Method is GET
			$referer - @String: Referral URL String - Optional
			$useragent - @String: User Agent String to send with request
	*/
	
	public static function Load ($url, $POST = NULL, $referer = NULL, $useragent = NULL)
	{
		$ch = curl_init();
		
		$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$HTTP_HOST = $_SERVER['HTTP_HOST'].'/';
		$referer = $referer ?: ( $protocol . $HTTP_HOST );
		$useragent = $useragent ?: self::$load['useragent'];

		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $referer);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $useragent);

		if (is_array($POST)):

			curl_setopt($ch, CURLOPT_POST, count($POST));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($POST));

		endif;

		$data = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		if (!$POST || is_array($POST)) return $status == 200 ? $data : $status;

		$html = [];
		$doc = new DOMDocument();
		
		libxml_use_internal_errors(true);

		@$doc->loadHTML($data);
		
		libxml_clear_errors();

		return $doc;
	}
	
	/*
		Load MetaData from an external URL - Returns a preview of a URL
		ARGUMENTS:
			$url - @String: The URL to load
			$options - @Array: Properties to load from the return object
			$encode - @Boolean: Encode the images as Data URL
	*/
	
	public static function Metadata ($url = NULL, $options = NULL, $encode = false, $params = [])
	{
		$options = $options ?: self::$metadata['options'];
		
		$dom = Self::Load($url, true);
		$parsed = parse_url($url);
		$extensions = ['jpg', 'png', 'gif'];
		
		if (!filter_var($url, FILTER_VALIDATE_URL)) return [ 
			"error" => true,
			"message" => "Invalid URL!"
		 ];

		$return = [
			'url' => $url
		];
		$return = array_merge($return, $parsed);

		# Parse Title

		$title = $dom->getElementsByTagName('title');

		if($title->item(0)) $return['title'] = $title->item(0)->nodeValue;

		# Parse Metas

		$metas = $dom->getElementsByTagName('meta');
		$meta_options = $options;

		for($i = 0; $i < $metas->length; $i++):

			$meta = $metas->item($i);
			$name = $meta->getAttribute('name') ?: $meta->getAttribute('property');

			foreach ($meta_options as $meta_option => $meta_value) 
			{
				$property = (is_string($meta_option)) ? $meta_option : $meta_value;
				
				if($name === $meta_option && !isset($return[$meta_value])) $return[$meta_value] = $meta->getAttribute('content');
			}

		endfor;

		$return['sitename'] = ArrayUtils::get($return, 'site_name') ?: ArrayUtils::get($return, 'host');
		
		# Encode image if applicable
		
		if ($encode && ArrayUtils::get($return, 'image')) ArrayUtils::set($return, 'image', Utils::Base64(ArrayUtils::get($return, 'image')));
		
		# Parse Background Images - If applicable
		
		$domain = ArrayUtils::get($parsed, 'scheme') ? ArrayUtils::get($parsed, 'scheme') . '://' . ArrayUtils::get($parsed, 'host') : NULL;
		$return['images'] = [];
		$added = [];
		$backgrounds = ArrayUtils::get($params, 'backgrounds');
		$backgrounds = $backgrounds ? intval($backgrounds) : $backgrounds;		
		
		if ($backgrounds):
		
			$tags = $dom->getElementsByTagName('*');
			
			foreach ($tags as $tag):
			
				$style = $tag->getAttribute('style');
				
				preg_match('~\bbackground(-image)?\s*:(.*?)\(\s*(\'|")?(?<image>.*?)\3?\s*\)~i', $style, $match);
				
				if (!count($match)) continue;
				
				# Get image Source
				
				$src = ArrayUtils::get($match, 'image');
				
				if (!$src) continue;
				
				# Validate src to make sure it is an image path
				
				$src = explode('?', $src);
				$src = reset($src);
				
				$extension = explode('.', $src);
				$extension = end($extension);
				
				if (!in_array($extension, $extensions)) continue;
				
				# Add Domain if none exists
				
				if (stripos($src, '//') === false):
				
					$src = stripos($src, '/') !== 0 ? "/{$src}" : $src;
					
					$src = $domain . $src;
					
				endif;
				
				if (in_array($src, $added) || count($added) >= $backgrounds) continue;
				
				array_push($added, $src);
				
				$src = $encode ? Utils::Base64($src) : $src;
				
				if ($src) array_push($return['images'], $src);
			
			endforeach;
		
		endif;
		
		# Parse Additional Images - If applicable
				
		$images = ArrayUtils::get($params, 'images');
		$images = $images ? intval($images) : $images;	
		
		if ($images):

			$tags = $dom->getElementsByTagName('img');
			
			foreach ($tags as $tag):
	
				# Get image Source
	
				$src = $tag->getAttribute('src');
		
				if(!$src || !strlen($src)) continue;
				
				# Check for valid URL in src
				
				preg_match("/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i", $src, $match);
				
				$src = ArrayUtils::get($match, 0) ?: $src;
		
				# Validate src to make sure it is an image path
				
				$src = explode('?', $src);
				$src = reset($src);
				
				$extension = explode('.', $src);
				$extension = end($extension);
				
				if (!in_array($extension, $extensions)) continue;
				
				# Add Domain if none exists
				
				if (stripos($src, '//') === false):
				
					$src = stripos($src, '/') !== 0 ? "/{$src}" : $src;
					
					$src = $domain . $src;
					
				endif;
				
				if (in_array($src, $added) || count($added) >= ($images + $backgrounds)) continue;
				
				array_push($added, $src);
				
				$src = $encode ? Utils::Base64($src) : $src;
				
				if ($src) array_push($return['images'], $src);
	
			endforeach;
		
		endif;

		return $return;
	}
}