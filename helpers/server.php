<?php

/*
	Author - PhilleepFlorence
	Description - Server Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;

class Server 
{	
	/*
		Server Utility - Attempts to locate the IP Address of the incoming REQUEST
	*/
	
	public static function ClientIP ()
	{
		$array = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR'];
		
		$ip = NULL;
		
		foreach ($array as $value)
		{
			if (!empty($_SERVER[$value]))
			{
				$ip = $_SERVER[$value];
				
				if (strpos($ip, ',') !== false)
				{
					$iplist = explode(',', $ip);
					
					foreach ($iplist as $currip)
					{
						if (strtolower($currip) === 'unknown' || $currip === false || $currip === -1) continue;
						
						$ip = $currip;
					}
				}
			}
		}
		
		if (strtolower($ip) === 'unknown' || $ip === false || $ip === -1) $ip = NULL;
				
		return $ip;
	}
	
	/*
		Server Utility - Wrapper to get the current domain
	*/
	
	public static function Domain ()
	{
		return ArrayUtils::get($_SERVER, 'HTTP_HOST') ?: ArrayUtils::get($_SERVER, 'SERVER_NAME');
	}
	
	/*
		Server Utility - Wrapper to get the current protocol
	*/
	
	public static function Protocol ()
	{
		return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	}
}