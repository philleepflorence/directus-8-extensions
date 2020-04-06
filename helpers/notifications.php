<?php

/*
	Author - PhilleepFlorence
	Description - cURL Helper Functions
*/

namespace Directus\Custom\Helpers;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateUtils;
use Directus\Util\StringUtils;

class Notifications 
{
	/*
		Push notifications via SMS, Push, or Email to Application Users
		ARGUMENTS:
			$params
				modes - @Array: Array of one or more modes of notification [sms, push, email]
				users - @Array: Array of User IDs
				message - @String: Message to send to users
	*/
	
	public static function Push ($params = [], $debug = false)
	{
		$modes = ArrayUtils::get($params, 'modes');
		$users = ArrayUtils::get($params, 'users');
		$message = ArrayUtils::get($params, 'message');
		
		$parameters = [
		    "status" => "published",
		    "limit" => -1,
		    "fields" => "id,username,first_name,last_name,email,telephone,notifications.*"		    
	    ];
	    
	    if (is_array($users)) ArrayUtils::set($parameters, 'filters.id.in', implode(',', $users));
		
		# Get users
		
		$tableGateway = Api::TableGateway("app_users", NULL);
		$entries = $tableGateway->getItems($parameters);
	    
	    $users = ArrayUtils::get($entries, 'data');
		
		# Get Notifications Settings for the users
		
		return [
			"success" => true,
			"message" => Api::Responses('notifications.push.success')
		];
	}
}