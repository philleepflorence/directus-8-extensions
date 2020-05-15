<?php

/*
	Author - PhilleepFlorence
	Description - cURL Helper Functions
*/

namespace Directus\Custom\Helpers;

use Directus\Application\Application;
use Directus\Mail\Mailer;
use Directus\Mail\Message;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateUtils;
use Directus\Util\StringUtils;

use function Directus\get_directus_setting;
use function Directus\get_kv_directus_settings;
use function Directus\send_mail_with_template;

class Notifications 
{
	/*
		Directus internal notifications via Email to API Users
		ARGUMENTS:
			$params
				template - @String: Filename of twig mail template - src/mail
				subject - @String: Subject sprinf template (project name variable is required!)
				user - @Array: User Array
	*/
	
	public static function Directus ($template, $subject, $user)
	{
		$data = [
	        "request" => Request::Properties(),
	        'settings' => get_kv_directus_settings(),		        
	        "user" => $user
        ];
        
       if (ArrayUtils::get($user, 'email')) 
       {
	       $response = send_mail_with_template($template, $data, function (Message $message) use ($subject, $user) {
	            $message->setSubject(
	                sprintf($subject, get_directus_setting('project_name', ''))
	            );
	            $message->setTo($user['email']);
	        }); 
	        
	        return $response;
		}
		
		return NULL;	
	}
	
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