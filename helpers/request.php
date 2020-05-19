<?php

/*
	Author - PhilleepFlorence
	Description - Request Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateTimeUtils;

use function Directus\get_request_host;

class Request 
{
	/*
		Request Utility - Parse Malformed Filters GET Queries into Filter GET queries if applicable
		
		@return array (GET)
	*/
	
	public static function Filters () 
	{
		$filters = ArrayUtils::get($_GET, 'filters');
		$filter = ArrayUtils::get($_GET, 'filter', []);
		$method = strtolower(ArrayUtils::get($_SERVER, 'REQUEST_METHOD'));
		$referer = ArrayUtils::get($_SERVER, 'HTTP_REFERER');
		
		if (is_array($filters) && $method === "get" && stripos($referer, '/admin/') && stripos($_SERVER["REQUEST_URI"], '/app/custom/') === false)
		{
			foreach ($filters as $key => $value)
			{
				$currkey = str_replace('filter[', '', $key);
				
				$filter[$currkey] = $value;
			}
			
			$_GET['filter'] = $filter;
			
			unset($_GET['filters']);
			
			$protocol = Server::Protocol();
			$domain = Server::Domain();
			$path = strtok($_SERVER["REQUEST_URI"],'?');
			$query = urldecode(http_build_query($_GET));
			
			return "{$protocol}{$domain}{$path}?{$query}";
		}		
				
		return false;
	}
		
	/*
		Request Utility - Process the Headers - Add POST Variables to the REQUEST Variable
		
		@return array (HEADERS)
	*/
	
	public static function Headers ()
	{
		$input = file_get_contents('php://input');
		
		# Parse GET Variables if the Global GET is empty!
		
		parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $query);		
		
		if (is_array($query) && count($query) && !count($_GET))
		{
			$_REQUEST = array_merge_recursive($_REQUEST, $query);
		}
	        
        if (is_string($input))
        {
	        $input = json_decode($input, true);
		    
		    if (is_array($input) && json_last_error() == JSON_ERROR_NONE)
		    {
			    $_REQUEST = array_merge_recursive($_REQUEST, $input);
		    }
	    
		    if (ArrayUtils::get($_REQUEST, 'debug') === 'GET') die(json_encode($_REQUEST));
		    
		    $headers = getallheaders();
		    
		    $ipaddress = isset($headers['Ip-Address']) ? $headers['Ip-Address'] : NULL;
		    
		    if ($ipaddress) 
		    {
			    $_SERVER['X_FORWARDED_FOR'] = $ipaddress;
			    $_SERVER['CLIENT_IP'] = $ipaddress;
			    $_SERVER['REMOTE_ADDR'] = $ipaddress;
		    }
        }
        
        return $headers;
	}
	
	/*
		Request Utility - Get Common Properties for the incoming request
	*/
	
	public static function Properties () 
	{
		return [
			'date' => DateTimeUtils::nowInUTC()->toString(),
			'user_agent' => ArrayUtils::get($_SERVER, 'HTTP_USER_AGENT'),
			'ip' => get_request_host()
		];
	}
}