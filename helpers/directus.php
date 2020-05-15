<?php

/*
	Author - PhilleepFlorence
	Description - Directus Cache Helper Functions
*/

namespace Directus\Custom\Helpers;

use Directus\Mail\Mailer;
use Directus\Mail\Message;
	
use Directus\Util\ArrayUtils;
use Directus\Util\DateTimeUtils;

use function Directus\base_path;
use function Directus\get_directus_setting;
use function Directus\get_kv_directus_settings;
use function Directus\parse_body;
use function Directus\send_mail_with_template;

class Directus 
{
	/*
		Load configuration and data for the Directus External Script(s)
	*/
	
	public static function App ($params)
	{
		$token = ArrayUtils::get($params, 'super_admin_token');
		$user = ArrayUtils::get($params, 'user');
		
		$headers = getallheaders();
		$referer = ArrayUtils::get($_SERVER, 'HTTP_REFERER') ?: ArrayUtils::get($headers, 'Referer');
		$domain = Server::Domain();
		
		$admin = Api::SuperAdmin ($token, false);
		
		$response = [
			"meta" => [
				"referer" => $referer,
				"admin" => $admin
			],
			"data" => []
		];
		
		if (!$admin && !stripos($referer, $domain)) 
		{
			http_response_code(401); 
			
			return $response;
		}
		
		# Load Directus Settings
		
		ArrayUtils::set($response, "data.settings", Api::Settings(true));
		
		# Load Directus User Metadata Information
		
		$tableGateway = Api::TableGateway('joins_directus_users_metadata', false);
		$items = $tableGateway->getItems([
			"filter" => [
				"user" => $user
			]
		]);
		$items = ArrayUtils::get($items, "data");
		
		ArrayUtils::set($response, "data.toured", $items);
		
		# Load Directus Collections
		
		$tableGateway = Api::TableGateway('directus_collections', NULL);
		$items = $tableGateway->getItems();
		$items = ArrayUtils::get($items, "data");
		
		ArrayUtils::set($response, "data.collections", $items);
		
		# Load Tour Options
		
		$tableGateway = Api::TableGateway('app_collections_configuration', NULL);
		$items = $tableGateway->getItems([
			"status" => "published",
			"filter" => [
				"slug" => "tours",
				"type" => "tour"
			]
		]);
		$items = ArrayUtils::get($items, "data.0.options");
		
		ArrayUtils::set($response, "data.tours", $items);
		
		return $response;
	}
	
	/*
		Directus internal mailer to API Users
		ARGUMENTS:
			$params
				template - @String: Filename of twig mail template - src/mail
				subject - @String: Subject sprinf template (project name variable is required!)
				users - @Array: User Array
	*/
	
	public static function Mail ($params)
	{
		$template = ArrayUtils::get($params, 'template', 'user.twig');
		$subject = ArrayUtils::get($params, 'subject');
		$users = ArrayUtils::get($params, 'users');
		$body = ArrayUtils::get($params, 'body');
		
		$data = [
	        "request" => Request::Properties(),
	        "settings" => get_kv_directus_settings(),
	        "body" => $body
        ];
        
        $response = [
	        "meta" => [
		        "mode" => "mail",
		        "template" => $template,
		        "message" => Api::Responses('directus.email.error')
	        ],
	        "data" => []	        
        ];
        
        $tableGateway = Api::TableGateway('directus_users', false);
        
        foreach ($users as $user)
        {
	        if (is_numeric($user))
	        {
		        $user = $tableGateway->getItems([ 
			        "filter" => [
				        "id" => $user
			        ] 
		        ]);
		        $user = ArrayUtils::get($user, 'data.0');
	        }
        
			if (ArrayUtils::get($user, 'email')) 
			{
				ArrayUtils::set($data, 'user', $user);				
				ArrayUtils::set($data, 'body', $body);
				
				$mailed = send_mail_with_template($template, $data, function (Message $message) use ($subject, $user) {
			        $message->setSubject(
			            sprintf($subject, get_directus_setting('project_name', ''))
			        );
			        $message->setTo($user['email']);
			    }); 
			    
			    array_push($response['data'], [
				    "first_name" => ArrayUtils::get($user, 'first_name'),
				    "last_name" => ArrayUtils::get($user, 'last_name'),
				    "email" => ArrayUtils::get($user, 'email')
			    ]);
			}
        }
        
        ArrayUtils::set($response, 'meta.message', Api::Responses('directus.email.success'));
        ArrayUtils::set($response, 'meta.success', true);
		
		return $response;	
	}
	
	/*
		Post Directus User Metadata - Create Row
	*/
	
	public static function Metadata ($params, $debug = false)
	{
		$user = ArrayUtils::get($params, 'user');
				
		if (!$user) return http_response_code(401); 
		
		$update = [
			"user" => $user,
			"section" => ArrayUtils::get($params, "section"),
			"key" => ArrayUtils::get($params, "key"),
			"value" => ArrayUtils::get($params, "value"),
			"created" => ArrayUtils::get($params, "created", DateTimeUtils::now()->toString())
		];
		
		if ($debug) return $update;
		
		$tableGateway = Api::TableGateway('joins_directus_users_metadata', true);
		$tableGateway->createRecord($update);
		
		return [
			"meta" => [
				"collection" => "joins_directus_users_metadata"
			],
			"data" => $update
		];
	}
}